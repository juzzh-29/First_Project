#include <Wire.h>
#include <Adafruit_MLX90614.h> 
#include "MAX30105.h"
#include "spo2_algorithm.h"
#include <WiFi.h>
#include <HTTPClient.h>

// --- CONFIGURATION ---
const char* ssid = "Infinix";
const char* password = "11111111";
const char* serverBase = "http://10.186.228.1/Vitals1/"; 
const String apiUrl = "http://10.186.228.1/Vitals1/insert_data.php";
const String activeUserUrl = "http://10.186.228.1/Vitals1/get_active_user.php";

const int piezoPin = 32;
const int pressurePin = 4; // GPIO 4 (G4) for MPX5050DP Blood Pressure Sensor

Adafruit_MLX90614 mlx = Adafruit_MLX90614();
MAX30105 particleSensor;

enum State { WAITING_TO_START, TEMP_STATE, VITAL_STATE, PIEZO_STATE, BP_STATE, SUMMARY_STATE };
State currentState = WAITING_TO_START;

int currentPatientID = 0; 
float finalTemp = 0.0;
int32_t finalHR = 0, finalSpO2 = 0; 
int finalBreath = 0;
float finalBP = 0.0; // Captures average/MAP pressure

uint32_t irBuffer[100]; 
uint32_t redBuffer[100]; 

void setup() {
  Serial.begin(115200);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print("."); }
  Serial.println("\nWiFi Connected!");

  Wire.begin(); 
  if (!mlx.begin()) Serial.println("MLX90614 error.");
  if (!particleSensor.begin(Wire, I2C_SPEED_STANDARD)) {
    Serial.println("MAX30105 error.");
  } else {
    particleSensor.setup(60, 4, 2, 100, 411, 4096);
    Serial.println("System Ready.");
  }

  analogReadResolution(12); // ESP32-S3 standard resolution (0-4095)
}

void loop() {
  if (currentState == WAITING_TO_START) {
    static unsigned long lastCheck = 0;
    if (millis() - lastCheck > 2000) { 
      currentPatientID = getLoggedInUser();
      if (currentPatientID > 0) {
        String status = checkWebStatus(currentPatientID);
        if (status != "wait" && status != "captured" && status != "") {
          Serial.print("Trigger: "); Serial.println(status);
          if (status == "temp") currentState = TEMP_STATE;
          else if (status == "hr") currentState = VITAL_STATE;
          else if (status == "resp") currentState = PIEZO_STATE;
          else if (status == "bp") currentState = BP_STATE; // Triggered via web
        }
      }
      lastCheck = millis();
    }
  }

  switch (currentState) {
    case TEMP_STATE: runTemp(); break;
    case VITAL_STATE: runVitals(); break;
    case PIEZO_STATE: runPiezo(); break;
    case BP_STATE: runBloodPressure(); break;
    case SUMMARY_STATE: uploadToTempTable(); break;
  }
}

// --- SENSOR FUNCTIONS ---

void runTemp() {
  Serial.println("WAITING FOR FINGER (TEMP SENSOR)...");
  unsigned long startWaiting = millis();
  bool detected = false;

  while (millis() - startWaiting < 15000) {
    float check = mlx.readObjectTempC();
    if (check >= 31.0 && check <= 42.0) {
      Serial.println("Finger detected! Stabilizing for 5 seconds...");
      delay(5000); 
      finalTemp = mlx.readObjectTempC();
      Serial.print("Final Temp Captured: "); Serial.println(finalTemp);
      detected = true;
      break; 
    }
    delay(500);
  }
  if (!detected) finalTemp = 0.01;
  currentState = SUMMARY_STATE;
}

void runVitals() {
  Serial.println("WAITING FOR FINGER (MAX30105)...");
  unsigned long startWaiting = millis();
  bool fingerOn = false;

  while (millis() - startWaiting < 15000) {
    if (particleSensor.getIR() > 50000) {
      Serial.println("Finger detected! Stabilizing for 5 seconds...");
      delay(5000);
      fingerOn = true;
      break;
    }
    delay(200);
  }

  if (!fingerOn) {
    finalHR = 0; finalSpO2 = 0;
    currentState = SUMMARY_STATE;
    return;
  }

  for (byte i = 0 ; i < 100 ; i++) {
    while (particleSensor.available() == false) particleSensor.check(); 
    redBuffer[i] = particleSensor.getRed();
    irBuffer[i] = particleSensor.getIR();
    particleSensor.nextSample(); 
  }

  int32_t spo2, hr; 
  int8_t validSPO2, validHR;
  maxim_heart_rate_and_oxygen_saturation(irBuffer, 100, redBuffer, &spo2, &validSPO2, &hr, &validHR);
  
  finalHR = (validHR == 1 && hr > 40) ? hr : 0;
  finalSpO2 = (validSPO2 == 1 && spo2 > 70) ? spo2 : 0;
  
  currentState = SUMMARY_STATE;
}

void runPiezo() {
  Serial.println("Respiration measure (15s)...");
  unsigned long start = millis(); 
  int count = 0; 
  bool active = false;
  
  while (millis() - start < 15000) {
    int val = analogRead(piezoPin);
    if (val > 700 && !active) { count++; active = true; delay(300); } 
    if (val < 400) active = false;
  }
  finalBreath = count * 4;
  currentState = SUMMARY_STATE;
}

void runBloodPressure() {
  float currentMmHg = 0;
  bool reachedTarget = false;
  
  Serial.println("\n--- BLOOD PRESSURE MEASUREMENT ---");
  Serial.println("Instruction: START PUMPING NOW...");

  // PHASE 1: Wait for inflation to 170 mmHg
  while (!reachedTarget) {
    int raw = analogRead(pressurePin);
    float voltAtPin = (raw / 4095.0) * 3.3;
    float vOut = voltAtPin * 1.5; // Multiplier for 3-resistor chain (5V to 3.3V)
    float kPa = (vOut / 5.0 - 0.04) / 0.018;
    currentMmHg = kPa * 7.50062;

    if (currentMmHg < 0) currentMmHg = 0;
    Serial.print("Current Pressure: "); Serial.print(currentMmHg, 0); Serial.println(" mmHg");

    if (currentMmHg >= 170.0) {
      reachedTarget = true;
      Serial.println("TARGET REACHED! STOP PUMPING!");
      Serial.println("Action: RELEASE VALVE SLOWLY...");
      delay(2000); 
    }
    delay(200); 
  }

  // PHASE 2: Recording while pressure drops
  unsigned long startRecord = millis();
  float sumBP = 0;
  int samples = 0;

  while (millis() - startRecord < 15000) {
    int raw = analogRead(pressurePin);
    float voltAtPin = (raw / 4095.0) * 3.3;
    float vOut = voltAtPin * 1.5;
    float kPa = (vOut / 5.0 - 0.04) / 0.018;
    float mmHg = kPa * 7.50062;

    if (mmHg > 0) {
      sumBP += mmHg;
      samples++;
    }
    delay(100);
  }

  finalBP = (samples > 0) ? (sumBP / samples) : 0;
  Serial.print("Final BP Reading: "); Serial.println(finalBP);
  currentState = SUMMARY_STATE;
}

void uploadToTempTable() {
  if(WiFi.status() == WL_CONNECTED){
    HTTPClient http;
    http.begin(String(serverBase) + "insert_data.php"); 
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    
    String postData = "patient_id=" + String(currentPatientID);
    postData += "&temp=" + String(finalTemp);
    postData += "&hr=" + String(finalHR);
    postData += "&ox=" + String(finalSpO2);
    postData += "&resp=" + String(finalBreath);
    postData += "&bp=" + String(finalBP); // New BP data column

    Serial.println("Uploading session data...");
    int httpResponseCode = http.POST(postData);
    
    if (httpResponseCode > 0) {
       Serial.print("Upload Done. Server: ");
       Serial.println(http.getString());
    }
    http.end();
  }
  currentState = WAITING_TO_START;
}

int getLoggedInUser() {
  HTTPClient http;
  http.begin(String(serverBase) + "get_active_user.php"); 
  int code = http.GET();
  if (code == 200) return http.getString().toInt();
  return 0;
}

String checkWebStatus(int id) {
  HTTPClient http;
  http.begin(String(serverBase) + "check_status.php?id=" + String(id));
  int code = http.GET();
  if (code == 200) return http.getString();
  return "wait";
}