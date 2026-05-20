<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Setup | VITALS</title>
    <link rel="stylesheet" href="setup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Ensuring the address field looks good without changing your external CSS */
        .input-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: 1rem;
            resize: vertical;
            transition: border-color 0.3s;
        }
        .input-group textarea:focus {
            outline: none;
            border-color: var(--neon, #0056b3);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar__container">
            <a href="#" id="navbar__logo"><i class="fa-solid fa-heart-pulse"></i> VITALS</a>
        </div>
    </nav>

    <div class="setup-container">
        <form id="profile-form" class="setup-card">
            <div class="setup-header">
                <i class="fa-solid fa-id-card-clip"></i>
                <h1>Profile Setup</h1>
                <p style="color: #666; font-size: 0.9rem;">Please complete your medical profile</p>
            </div>

            <div class="input-group">
                <label>Confirm Full Name</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars($_SESSION['fullname']); ?>" required>
            </div>

            <div class="input-row">
                <div class="input-group">
                    <label>Age</label>
                    <input type="number" name="age" placeholder="--" required>
                </div>
                <div class="input-group">
                    <label>Sex</label>
                    <select name="sex">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <div class="input-row">
                <div class="input-group">
                    <label>Weight (kg)</label>
                    <input type="number" name="weight" placeholder="--" required>
                </div>
                <div class="input-group">
                    <label>Height (cm)</label>
                    <input type="number" name="height" placeholder="--" required>
                </div>
            </div>

            <div class="input-group">
                <label>Home Address</label>
                <textarea name="address" rows="3" placeholder="Street, Brgy, City, Province" required></textarea>
            </div>

            <button type="submit" class="done-btn">DONE</button>
        </form>
    </div>

    <script>
        document.getElementById('profile-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            // Visual feedback on button
            const btn = document.querySelector('.done-btn');
            const originalText = btn.innerText;
            btn.innerText = "SAVING...";
            btn.style.opacity = "0.7";
            btn.disabled = true;

            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                if(data.trim() === "success") {
                    window.location.href = "dashboard.php";
                } else {
                    alert("Error updating profile: " + data);
                    btn.innerText = originalText;
                    btn.style.opacity = "1";
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert("Connection error. Please try again.");
                btn.innerText = originalText;
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>