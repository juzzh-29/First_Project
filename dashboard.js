/**
 * VITALS DASHBOARD LOGIC
 */

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const role = document.body.dataset.role;
    const targetId = document.body.dataset.target;

    // Toast logic
    if (urlParams.get('success') === '1') {
        const toast = document.getElementById("save-toast");
        if(toast) {
            toast.classList.add("show");
            setTimeout(() => { 
                toast.classList.remove("show");
                const newUrl = window.location.pathname + (urlParams.get('view_user_id') ? '?view_user_id=' + urlParams.get('view_user_id') : '');
                window.history.replaceState({}, document.title, newUrl);
            }, 3000);
        }
    }

    // Live Polling
    if (role === "0" || urlParams.has('view_user_id')) {
        setInterval(function() {
            const isTyping = document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA';
            if (isTyping) return;

            const cacheBuster = new Date().getTime();
            fetch(`get_latest_vitals.php?id=${targetId}&_t=${cacheBuster}`)
                .then(response => response.json())
                .then(vitals => {
                    if (vitals.status === 'success') {
                        updateDashboardUI(vitals.data);
                    }
                })
                .catch(err => console.error("Poll error:", err));
        }, 2000);
    }
});

function updateDashboardUI(data) {
    syncVital('temp-val', 'temp-status', data.temperature, " °C", 'temp');
    syncVital('hr-val', 'hr-status', data.heart_rate, " BPM", 'hr');
    syncVital('spo2-val', 'spo2-status', data.spo2, "%", 'spo2');
    syncVital('bp-val', 'bp-status', data.blood_pressure, "", 'bp');
    syncVital('resp-val', 'resp-status', data.respiration, " BrPM", 'resp');
}

function syncVital(valId, statusId, value, unit, type) {
    const valEl = document.getElementById(valId);
    const statusEl = document.getElementById(statusId);
    if (!valEl || !statusEl) return;

    const hasData = value && value != 0 && value != '0' && value != '0/0' && value != '0.0';

    if (hasData) {
        valEl.innerText = value + unit;
        valEl.style.color = "#171010"; 

        let label = "NORMAL";
        let color = "#3af443";
        const v = parseFloat(value);

        if (type === 'temp') {
            if (v < 36.1 || v > 37.5) { label = "IRREGULAR"; color = "#ff4b5c"; }
        } else if (type === 'hr') {
            if (v < 60 || v > 100) { label = "IRREGULAR"; color = "#ff4b5c"; }
        } else if (type === 'spo2') {
            if (v < 94) { label = "IRREGULAR"; color = "#ff4b5c"; }
        } else if (type === 'resp') {
            if (v < 12 || v > 20) { label = "IRREGULAR"; color = "#ff4b5c"; }
        } else if (type === 'bp') {
             // Basic check for BP
             let parts = value.split('/');
             if(parseInt(parts[0]) > 120 || parseInt(parts[1]) > 80) { label = "IRREGULAR"; color = "#ff4b5c"; }
        }

        statusEl.innerText = label;
        statusEl.style.color = color;
    } else {
        if (statusEl.innerText.includes("CHECKING") || valEl.innerText.includes("SCANNING")) return; 
        valEl.innerText = "NO DATA";
        statusEl.innerText = "NO DATA";
        statusEl.style.color = "#888";
    }
}

function triggerSensor(type) {
    const targetId = document.body.dataset.target;
    const valEl = document.getElementById(type + "-val");
    const statusEl = document.getElementById(type + "-status");

    if(valEl) valEl.innerHTML = '<span style="color: #ffa500; font-size: 0.8em;">SCANNING...</span>';
    if(statusEl) {
        statusEl.innerText = "CHECKING...";
        statusEl.style.color = "#ffa500";
    }

    fetch('update_command.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'cmd=SCAN_' + type.toUpperCase() + '&id=' + targetId
    });
}

/**
 * NEW: RESET SENSOR FUNCTIONS
 */
function toggleResetMenu() {
    document.getElementById("reset-menu").classList.toggle("show");
}

// Close menu if clicked outside
window.onclick = function(event) {
    if (!event.target.closest('.reset-wrapper')) {
        const menu = document.getElementById("reset-menu");
        if (menu && menu.classList.contains('show')) menu.classList.remove('show');
    }
}

function resetSensor(type) {
    const targetId = document.body.dataset.target;
    
    // Optimistic UI update: Clear values immediately
    if (type === 'temp' || type === 'all') document.getElementById('temp-val').innerText = 'NO DATA';
    if (type === 'hr' || type === 'all') {
        document.getElementById('hr-val').innerText = 'NO DATA';
        document.getElementById('spo2-val').innerText = 'NO DATA';
    }
    if (type === 'bp' || type === 'all') document.getElementById('bp-val').innerText = 'NO DATA';
    if (type === 'resp' || type === 'all') document.getElementById('resp-val').innerText = 'NO DATA';

    fetch(`reset_sensor.php?type=${type}&id=${targetId}`)
        .then(() => {
            console.log("Sensor reset:", type);
            toggleResetMenu();
        });
}

function filterUsers() {
    // Get the search string
    let input = document.getElementById("userSearch");
    let filter = input.value.toUpperCase();
    let table = document.getElementById("userTable");
    let tr = table.getElementsByTagName("tr");

    // Loop through all table rows (skipping the header)
    for (let i = 1; i < tr.length; i++) {
        // Look specifically at the "Full Name" column (the second <td>, index 1)
        let td = tr[i].getElementsByTagName("td")[1];
        if (td) {
            let txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = ""; // Show row
            } else {
                tr[i].style.display = "none"; // Hide row
            }
        }
    }
}