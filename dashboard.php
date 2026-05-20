<?php
/**
 * MAIN DASHBOARD
 */
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$role = (int)$_SESSION['role'];
$user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

// Fix: Identify who we are looking at without kicking other users off
$target_id = (isset($_GET['view_user_id']) && $role == 1) ? mysqli_real_escape_string($conn, $_GET['view_user_id']) : $user_id;

// Logic Fix: Only mark the current session user as active, and the target patient if Admin is viewing
mysqli_query($conn, "UPDATE users SET is_active = 1 WHERE id = '$user_id'"); 
if ($role == 1 && isset($_GET['view_user_id'])) {
    mysqli_query($conn, "UPDATE users SET is_active = 1 WHERE id = '$target_id'");
}

if (!isset($_SESSION['fresh_login'])) {
    mysqli_query($conn, "UPDATE users SET scan_command = 'IDLE' WHERE id = '$target_id'");
    mysqli_query($conn, "DELETE FROM vitals_temp WHERE patient_id = '$target_id'");
    $_SESSION['fresh_login'] = true;
}

// Added 'address' to the SELECT query
$user_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, fullname, age, sex, weight, height, address FROM users WHERE id = '$target_id'"));
$live_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM vitals_temp WHERE patient_id = '$target_id' ORDER BY id DESC LIMIT 1"));
$latest_vitals = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM vitals_history WHERE patient_id = '$target_id' ORDER BY created_at DESC LIMIT 1"));

function getStatus($val, $type) {
    if (empty($val) || $val == 0 || $val == '0' || $val == '0/0') return ['label' => 'No Data', 'color' => '#888'];
    $v = (float)$val;
    switch ($type) {
        case 'hr': return ($v < 60 || $v > 100) ? ['label' => 'Irregular', 'color' => '#ff4b5c'] : ['label' => 'Normal', 'color' => '#3af443'];
        case 'temp': return ($v < 36.1 || $v > 37.5) ? ['label' => 'Irregular', 'color' => '#ff4b5c'] : ['label' => 'Normal', 'color' => '#3af443'];
        case 'spo2': return ($v < 94) ? ['label' => 'Low Oxygen', 'color' => '#ff4b5c'] : ['label' => 'Normal', 'color' => '#3af443'];
        case 'resp': return ($v < 12 || $v > 20) ? ['label' => 'Irregular', 'color' => '#ff4b5c'] : ['label' => 'Normal', 'color' => '#3af443'];
        case 'bp': 
            $bp_parts = explode('/', $val);
            $sys = (int)$bp_parts[0];
            $dia = (int)($bp_parts[1] ?? 0);
            return ($sys > 120 || $dia > 80) ? ['label' => 'Irregular', 'color' => '#ff4b5c'] : ['label' => 'Normal', 'color' => '#3af443'];
    }
}

$sensors = [
    'temp' => ['label' => 'TEMPERATURE', 'icon' => 'fa-thermometer-half', 'color' => '#ffa500'],
    'hr'   => ['label' => 'HR & SpO2', 'icon' => 'fa-heart-pulse', 'color' => '#ff4b2b'],
    'bp'   => ['label' => 'BLOOD PRESSURE', 'icon' => 'fa-droplet', 'color' => '#a855f7'],
    'resp' => ['label' => 'RESPIRATION', 'icon' => 'fa-lungs', 'color' => '#3af443']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | VITALS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body data-role="<?= $role ?>" data-target="<?= $target_id ?>">

<div id="save-toast"><i class="fa-solid fa-circle-check"></i> Vitals Saved Successfully!</div>

<nav class="navbar">
    <div class="nav-container">
        <a href="dashboard.php" id="logo"><i class="fa-solid fa-heart-pulse"></i> VITALS</a>
        <div>
            <span class="user-greeting">Hello, <?= htmlspecialchars($_SESSION['fullname']) ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <?php if ($role == 1 && !isset($_GET['view_user_id'])): ?>
        <section class="section-box admin-section">
            <h3 class="box-title admin-title"><i class="fa-solid fa-users-gear"></i> User Management</h3>
            <div class="search-container" style="margin-bottom: 15px;">
                <div class="search-wrapper" style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #888;"></i>
                    <input type="text" id="userSearch" onkeyup="filterUsers()" placeholder="Search patient by name..." 
                           style="width: 100%; padding: 12px 15px 12px 40px; border-radius: 10px; border: 1px solid #ddd; font-family: 'Kumbh Sans', sans-serif; font-size: 1rem;">
                </div>
            </div>
            <div class="table-wrap">
                <table class="admin-table-modern" id="userTable">
                    <thead>
                        <tr><th>ID</th><th>Full Name</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $all_users = mysqli_query($conn, "SELECT id, fullname, age FROM users WHERE role = 0 ORDER BY id ASC");
                        while($u = mysqli_fetch_assoc($all_users)): ?>
                        <tr>
                            <td class="col-id">#<?= $u['id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['fullname']) ?></strong></td>
                            <td><span class="status-pill <?= $u['age'] ? 'complete' : 'pending' ?>"><?= $u['age'] ? 'COMPLETE' : 'PENDING' ?></span></td>
                            <td><a href="dashboard.php?view_user_id=<?= $u['id'] ?>" class="btn-view-vitals"><i class="fa-solid fa-eye"></i> View Vitals</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php else: ?>
        <?php if ($role == 1): ?>
            <a href="dashboard.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Patient List</a>
        <?php endif; ?>

        <section class="section-box">
            <h3 class="box-title"><i class="fa-solid fa-user"></i> Viewing Record: <?= htmlspecialchars($user_data['fullname']) ?></h3>
            <div class="info-grid">
                <div class="info-card"><small>AGE</small><p><?= $user_data['age'] ?? '--' ?></p></div>
                <div class="info-card"><small>SEX</small><p><?= $user_data['sex'] ?? '--' ?></p></div>
                <div class="info-card"><small>WEIGHT</small><p><?= $user_data['weight'] ? $user_data['weight'].'kg' : '--' ?></p></div>
                <div class="info-card"><small>HEIGHT</small><p><?= $user_data['height'] ? $user_data['height'].'cm' : '--' ?></p></div>
                <div class="info-card" style="grid-column: span 2;">
                    <small>ADDRESS</small>
                    <p style="font-size: 0.9rem; line-height: 1.4;"><?= !empty($user_data['address']) ? htmlspecialchars($user_data['address']) : '--' ?></p>
                </div>
            </div>
        </section>

        <section class="section-box">
            <div class="monitor-layout">
                <div class="mon-left">
                    <h3 class="box-title"><i class="fa-solid fa-clipboard-check"></i> STATUS</h3>
                    <div class="live-list">
                        <?php 
                        $v_conf = [
                            ['l'=>'HEART RATE', 'k'=>'heart_rate', 'id'=>'hr-val', 'sid'=>'hr-status', 't'=>'hr', 'c'=>'#ff4b2b', 'u'=>'BPM'],
                            ['l'=>'TEMP', 'k'=>'temperature', 'id'=>'temp-val', 'sid'=>'temp-status', 't'=>'temp', 'c'=>'#ffa500', 'u'=>'°C'],
                            ['l'=>'SPO2', 'k'=>'spo2', 'id'=>'spo2-val', 'sid'=>'spo2-status', 't'=>'spo2', 'c'=>'#00d2ff', 'u'=>'%'],
                            ['l'=>'BP', 'k'=>'blood_pressure', 'id'=>'bp-val', 'sid'=>'bp-status', 't'=>'bp', 'c'=>'#a855f7', 'u'=>''],
                            ['l'=>'RESP', 'k'=>'respiration', 'id'=>'resp-val', 'sid'=>'resp-status', 't'=>'resp', 'c'=>'#3af443', 'u'=>'BrPM']
                        ];
                        foreach($v_conf as $v):
                            $val = $live_row[$v['k']] ?? '0';
                            $st = getStatus($val, $v['t']);
                        ?>
                        <div class="status-item" style="border-left: 4px solid <?= $v['c'] ?>">
                            <div class="status-content">
                                <small class="status-label"><?= $v['l'] ?></small>
                                <strong class="status-value" id="<?= $v['id'] ?>">
                                    <?= ($val == 0 || $val == '0' || $val == '0/0') ? 'NO DATA' : $val . ' ' . $v['u'] ?>
                                </strong>
                            </div>
                            <b class="status-badge" id="<?= $v['sid'] ?>" style="color:<?= $st['color'] ?>"><?= strtoupper($st['label']) ?></b>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="mon-right">
                    <p class="scan-hint"><?= ($role == 1) ? 'View-Only Mode' : 'Select a sensor to begin scanning' ?></p>
                    <div class="scan-grid">
                        <?php foreach($sensors as $k => $s): 
                            // Logic: Disable buttons if logged in as Admin
                            $isAdmin = ($role == 1);
                            $clickAction = $isAdmin ? "return false;" : "triggerSensor('$k')";
                            $disabledClass = $isAdmin ? "btn-disabled" : "";
                        ?>
                            <button type="button" onclick="<?= $clickAction ?>" class="scan-btn <?= $disabledClass ?>" style="border-color:<?= $s['color'] ?>; color:<?= $s['color'] ?>;">
                                <i class="fa-solid <?= $s['icon'] ?>"></i><br>
                                <small><?= $s['label'] ?></small>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="action-footer">
                        <?php if ($role != 1): ?>
                            <a href="save_record.php?id=<?= $target_id ?>" class="save-icon" title="Save Data"><i class="fa-solid fa-floppy-disk"></i></a>
                        <?php endif; ?>
                        
                       <?php if ($role === 1): ?>
    <a href="javascript:void(0)" onclick="handlePrint()" class="print-icon" title="Print Report"><i class="fa-solid fa-print"></i></a>
<?php else: ?>
    <a href="javascript:void(0)" onclick="openPrintNotice()" class="print-icon" title="Admin Only" style="opacity: 0.5;"><i class="fa-solid fa-print"></i></a>
<?php endif; ?>
                        <?php if ($role != 1): ?>
                        <div class="reset-wrapper">
                            <a href="javascript:void(0)" onclick="toggleResetMenu()" class="delete-icon" title="Reset Sensor"><i class="fa-solid fa-arrow-rotate-left"></i></a>
                            <div id="reset-menu" class="reset-menu-content">
                                <small>Reset Sensor</small>
                                <a href="javascript:void(0)" onclick="resetSensor('temp')"><i class="fa-solid fa-thermometer-half"></i> Temperature</a>
                                <a href="javascript:void(0)" onclick="resetSensor('hr')"><i class="fa-solid fa-heart-pulse"></i> HR & SpO2</a>
                                <a href="javascript:void(0)" onclick="resetSensor('bp')"><i class="fa-solid fa-droplet"></i> Blood Pressure</a>
                                <a href="javascript:void(0)" onclick="resetSensor('resp')"><i class="fa-solid fa-lungs"></i> Respiration</a>
                                <a href="javascript:void(0)" onclick="resetSensor('all')" style="color: var(--danger); border-top: 1px solid #eee;">
                                    <i class="fa-solid fa-trash-can"></i> Reset All
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-box">
            <h3 class="box-title"><i class="fa-solid fa-history"></i> LAST RECORDED VITALS</h3>
            <div class="history-grid">
                <?php 
                $cards = [
                    ['t'=>'Heart Rate', 'v'=>$latest_vitals['heart_rate']??'0', 'u'=>'BPM', 'i'=>'fa-heart-pulse', 'cl'=>'hr'],
                    ['t'=>'Temperature', 'v'=>$latest_vitals['temperature']??'0.0', 'u'=>'°C', 'i'=>'fa-thermometer-half', 'cl'=>'temp'],
                    ['t'=>'Blood Pressure', 'v'=>$latest_vitals['blood_pressure']??'0/0', 'u'=>'mmHg', 'i'=>'fa-droplet', 'cl'=>'bp'],
                    ['t'=>'Oxygen Level', 'v'=>$latest_vitals['spo2']??'0', 'u'=>'%', 'i'=>'fa-wind', 'cl'=>'spo2'],
                    ['t'=>'Respiration', 'v'=>$latest_vitals['respiration']??'0', 'u'=>'BrPM', 'i'=>'fa-lungs', 'cl'=>'resp']
                ];
                foreach($cards as $c): ?>
                <div class="history-card">
                    <div class="card-header"><span><?= $c['t'] ?></span><i class="fa-solid <?= $c['i'] ?> icon-<?= $c['cl'] ?>"></i></div>
                    <div class="card-body"><span class="val"><?= $c['v'] ?></span><span class="unit <?= $c['cl'] ?>-unit"><?= $c['u'] ?></span></div>
                    <div class="card-footer">• LAST RECORD</div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section-box history-table-section">
            <h3 class="box-title"><i class="fa-solid fa-clock-rotate-left"></i> FULL VITALS HISTORY</h3>
            <div class="table-wrap">
                <table class="admin-table-modern">
                    <thead>
                        <tr><th>Date & Time</th><th>Temp</th><th>HR</th><th>SpO2</th><th>BP</th><th>Resp</th><th>Notes</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $hist_res = mysqli_query($conn, "SELECT * FROM vitals_history WHERE patient_id = '$target_id' ORDER BY created_at DESC");
                        if (mysqli_num_rows($hist_res) > 0):
                            while($row = mysqli_fetch_assoc($hist_res)): ?>
                            <tr>
                                <td><?= date('M d, Y | H:i', strtotime($row['created_at'])) ?></td>
                                <td class="val-temp"><?= $row['temperature'] ? $row['temperature'].'°C' : '--' ?></td>
                                <td class="val-hr"><?= $row['heart_rate'] ? $row['heart_rate'].' BPM' : '--' ?></td>
                                <td class="val-spo2"><?= $row['spo2'] ? $row['spo2'].'%' : '--' ?></td>
                                <td class="val-bp"><?= $row['blood_pressure'] ?: '--' ?></td>
                                <td class="val-resp"><?= $row['respiration'] ? $row['respiration'].' BrPM' : '--' ?></td>
                                <td>
                                    <?php if ($role == 1): ?>
                                        <form action="save_note.php" method="POST" class="note-form">
                                            <input type="hidden" name="vitals_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="redirect_id" value="<?= $target_id ?>">
                                            <input type="text" name="note_text" class="note-input" value="<?= htmlspecialchars($row['notes'] ?? '') ?>" placeholder="Add note...">
                                            <button type="submit" class="note-save"><i class="fa-solid fa-check"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="note-display"><?= htmlspecialchars($row['notes'] ?? 'No notes') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile;
                        else: ?>
                            <tr><td colspan="7" style="text-align:center; padding:20px;">No records.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</div>

<div id="print-section">
  <div class="print-header">
    <h2><?php echo htmlspecialchars($user_data['fullname'] ?? 'Unknown Patient'); ?></h2>
    <p style="margin:2px 0 6px;"><?php echo htmlspecialchars($user_data['address'] ?? 'No address on record'); ?></p>
    <p>Patient ID #<?php echo htmlspecialchars($user_data['id'] ?? '—'); ?> &nbsp;·&nbsp; Printed: <span id="print-time"></span></p>
  </div>

  <script>
    document.getElementById('print-time').textContent = new Date().toLocaleString('en-US', {
      year: 'numeric', month: 'long', day: 'numeric',
      hour: 'numeric', minute: '2-digit', hour12: true
    });
  </script>

  <table class="print-table">
    <thead><tr><th colspan="2">Patient Information</th></tr></thead>
    <tbody>
      <tr><td>Age</td>       <td><?php echo htmlspecialchars($user_data['age'] ?? '—'); ?> yrs</td></tr>
      <tr><td>Sex</td>       <td><?php echo htmlspecialchars($user_data['sex'] ?? '—'); ?></td></tr>
      <tr><td>Weight</td>    <td><?php echo htmlspecialchars($user_data['weight'] ?? '—'); ?> kg</td></tr>
      <tr><td>Height</td>    <td><?php echo htmlspecialchars($user_data['height'] ?? '—'); ?> cm</td></tr>
    </tbody>
  </table>

  <table class="print-table">
    <thead><tr><th colspan="2">Vitals <span class="ts"><?php echo htmlspecialchars($live_row['recorded_at'] ?? '—'); ?></span></th></tr></thead>
    <tbody>
      <?php if ($live_row): ?>
      <tr><td>Heart Rate</td>      <td><?php echo htmlspecialchars($live_row['heart_rate'] ?? '—'); ?> BPM</td></tr>
      <tr><td>SpO₂</td>            <td><?php echo htmlspecialchars($live_row['spo2'] ?? '—'); ?> %</td></tr>
      <tr><td>Temperature</td>     <td><?php echo htmlspecialchars($live_row['temperature'] ?? '—'); ?> °C</td></tr>
      <tr><td>Blood Pressure</td>  <td><?php echo htmlspecialchars($live_row['blood_pressure'] ?? '—'); ?> mmHg</td></tr>
      <tr><td>Respiratory Rate</td><td><?php echo htmlspecialchars($live_row['respiration'] ?? '—'); ?> BrPM</td></tr>
      <?php else: ?><tr><td colspan="2">No vitals available</td></tr><?php endif; ?>
    </tbody>
  </table>


<div class="sig-row">
    <div class="sig-left">
      <p class="sig-label">Assisted by</p>
      <p class="sig-printed" id="nurseName">—</p>
      <p class="sig-role">Nurse on Duty</p>
    </div>
    <div class="sig-right">
      <div class="sig-space"></div>
      <div class="sig-line"></div>
      <p class="sig-role">Signature</p>
    </div>
  </div>

</div>

<!-- Nurse Name Modal -->
<div id="nurseNameModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
  <div style="background:#fff; padding:30px; border-radius:10px; width:320px; text-align:center; box-shadow:0 4px 20px rgba(0,0,0,0.2);">
    <h3 style="margin:0 0 15px;">Enter Nurse Name</h3>
    <input type="text" id="nurseNameInput" placeholder="Type name here..." style="width:100%; padding:10px; font-size:14px; border:1px solid #ccc; border-radius:6px; box-sizing:border-box;">
    <div style="margin-top:15px; display:flex; gap:10px; justify-content:center;">
      <button onclick="confirmPrint()" style="padding:10px 24px; background:#0056b3; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:14px;">Print</button>
      <button onclick="cancelPrint()" style="padding:10px 24px; background:#ccc; color:#333; border:none; border-radius:6px; cursor:pointer; font-size:14px;">Cancel</button>
    </div>
  </div>
</div>

<div id="printNoticeModal" class="modal-overlay">
    <div class="modal-content">
        <div style="font-size: 3rem; color: #0056b3; margin-bottom: 15px;"><i class="fa-solid fa-user-lock"></i></div>
        <h3>Admin Access Only</h3>
        <p>Please ask the <strong>Admin</strong> to print this report for you.</p>
        <button class="close-notice-btn" onclick="closePrintNotice()">Got it</button>
    </div>
</div>

<script>
  function handlePrint() {
    const modal = document.getElementById('nurseNameModal');
    modal.style.display = 'flex';
    document.getElementById('nurseNameInput').value = '';
    document.getElementById('nurseNameInput').focus();
  }

  function confirmPrint() {
    const input = document.getElementById('nurseNameInput').value.trim();
    if (!input) {
      alert('Please enter a name before printing.');
      return;
    }
    document.getElementById('nurseName').textContent = input;
    document.getElementById('nurseNameModal').style.display = 'none';
    setTimeout(() => window.print(), 300);
  }

  function cancelPrint() {
    document.getElementById('nurseNameModal').style.display = 'none';
  }

  // Also allow pressing Enter to confirm
  document.getElementById('nurseNameInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') confirmPrint();
  });

function openPrintNotice() {
    document.getElementById('printNoticeModal').style.display = 'flex';
}
function closePrintNotice() {
    document.getElementById('printNoticeModal').style.display = 'none';
}
function filterUsers() {
    let input = document.getElementById("userSearch");
    let filter = input.value.toUpperCase();
    let table = document.getElementById("userTable");
    let tr = table.getElementsByTagName("tr");
    for (let i = 1; i < tr.length; i++) {
        let td = tr[i].getElementsByTagName("td")[1];
        if (td) {
            let txtValue = td.textContent || td.innerText;
            tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
        }
    }
}
window.onclick = function(event) {
    if (event.target == document.getElementById('printNoticeModal')) {
        closePrintNotice();
    }
}
</script>
<script src="dashboard.js"></script>
</body>
</html>