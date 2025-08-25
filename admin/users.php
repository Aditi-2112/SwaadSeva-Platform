<?php
session_start();
if ($_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

include '../db_connect.php';
// Fetch users from the database
$users_result = $conn->query("SELECT * FROM users");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Users | SwaadSeva Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/admin-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    input[type="text"] {
      padding: 6px 10px;
      margin-right: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 15px;
    }
  </style>
</head>

<body>

  <!-- Sidebar -->
  <aside class="sidebar">
    <img src="assets/logo/logo5.png" alt="Logo Image">
    <div class="brand">SwaadSeva Admin</div>
    <ul class="sidebar-menu">
      <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
      <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
      <li><a href="orders.php"><i class="fas fa-box"></i> Orders</a></li>
      <li><a href="admin-event-orders.php"><i class="fas fa-calendar-alt"></i> Event Orders</a></li>
      <li><a href="menu.php"><i class="fas fa-utensils"></i> Menu</a></li>
      <li><a href="plans.php"><i class="fas fa-clipboard-list"></i> Plans</a></li>
      <li><a href="admin-feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a></li>
      <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
      <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
    </ul>
  </aside>

  <!-- Topbar -->
  <header class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle"><i class="fas fa-bars"></i></button>
    </div>
    <div class="topbar-right">
      <span class="admin-name">Hello, Admin</span>
      <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </header>

  <main class="admin-main">
    <div class="page-header">

      <!-- Users Plans Table   -->
      <h1 id="mainHeading">Users</h1>
      <p id="mainSubtext">Click "View" to see and edit user details.</p>
      <div style="margin-bottom:20px;">
        <button class="view-btn" id="viewPlansBtn" onclick="toggleUserPlans()">📋 View All User Plans</button>
      </div>
      <button class="view-btn" id="viewUsersBtn" onclick="toggleUserPlans()" style="display: none;">👥 View
        Users</button>
    </div>
    <div id="userPlansSection" style="display:none; margin-top: 20px;">
      <div class="section-header">
        <div style="margin: 15px 0;">
          <input type="text" id="searchUsername" placeholder="Search by Username" onkeyup="filterPlans()">
          <input type="text" id="searchPlan" placeholder="Search by Plan Name" onkeyup="filterPlans()">
        </div>
        <button class="add-btn" onclick="openAddPlanModal()">➕ Add User Plan</button>
      </div>

      <div class="responsive-table-container">
        <table class="responsive-table" id="userPlansTable">
          <thead>
            <tr>
              <th>Username</th>
              <th>Plan Name</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Days Left</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $plansQuery = $conn->query("SELECT up.id as upid, u.name AS username, p.name AS plan_name, up.start_date, up.end_date, p.total_price
                                    FROM user_plans up
                                    JOIN users u ON up.user_id = u.id
                                    JOIN plans p ON up.plan_id = p.id");

            $today = new DateTime();
            while ($row = $plansQuery->fetch_assoc()) {
              $end = new DateTime($row['end_date']);
              $daysLeft = $today > $end ? 0 : $today->diff($end)->days;

              echo "<tr>
                <td>{$row['username']}</td>
                <td>{$row['plan_name']}</td>
                <td>{$row['start_date']}</td>
                <td>{$row['end_date']}</td>
                <td>{$daysLeft}</td>
                <td><button class='view-btn' onclick='openPlanModal({$row['upid']})'>Edit</button></td>
              </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Users Table   -->
    <div class="section-header"
      style="margin: 15px auto; display: flex; flex-wrap: wrap; gap: 10px; justify-content: left;">
      <input type="text" id="searchUserName" placeholder="Search by Username" onkeyup="filterUsers()"
        style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; flex: 1 1 220px; max-width: 300px;">
      <input type="text" id="searchUserEmail" placeholder="Search by Email" onkeyup="filterUsers()"
        style="padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; flex: 1 1 220px; max-width: 300px;">
    </div>

    <div id="usersTableSection">
      <div class="responsive-table-container">
        <table class="responsive-table">
          <thead>
            <tr>
              <th>Sr No</th>
              <th>Name</th>
              <th>Email</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $i = 1;
            while ($row = $users_result->fetch_assoc()) {
              echo "<tr>
                <td>{$i}</td>
                <td>{$row['name']}</td>
                <td>{$row['email']}</td>
                <td><button class='view-btn' onclick=\"openModal({$row['id']})\">View</button></td>
              </tr>";
              $i++;
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>

  <!-- MODAL -->
  <div class="overlay" id="modalOverlay">
    <div class="modal">
      <button class="close-btn" onclick="closeModal()">&times;</button>
      <h3>User Details</h3>
      <div id="userDetails"></div>
      <button class="save-btn" onclick="saveChanges()">Save Changes</button>
    </div>
  </div>
  <!-- Plan Edit Modal -->
  <div class="overlay" id="planModalOverlay">
    <div class="modal">
      <button class="close-btn" onclick="closePlanModal()">&times;</button>
      <h3>Edit User Plan</h3>
      <form id="editPlanForm">
        <input type="hidden" name="user_plan_id" id="edit_user_plan_id">
        <label>Start Date: <input type="date" name="start_date" id="edit_start_date"></label><br><br>
        <label>End Date: <input type="date" name="end_date" id="edit_end_date"></label><br><br>
        <label>Total Price: <input type="number" name="total_price" id="edit_total_price"></label><br><br>
        <button type="submit" class="save-btn">Save Changes</button>
      </form>
    </div>
  </div>

  <!-- Add Plan Modal -->
  <div class="overlay" id="addPlanModalOverlay">
    <div class="modal">
      <button class="close-btn" onclick="closeAddPlanModal()">&times;</button>
      <h3>Add User Plan</h3>
      <form id="addPlanForm">
        <label>User ID: <input type="number" name="user_id" required></label><br><br>
        <label>Plan ID: <input type="number" name="plan_id" required></label><br><br>
        <label>Start Date: <input type="date" name="start_date" required></label><br><br>
        <label>End Date: <input type="date" name="end_date" required></label><br><br>
        <label>Amount Paid: <input type="number" name="amount_paid" required></label><br><br>
        <button type="submit" class="save-btn">Add Plan</button>
      </form>
    </div>
  </div>

  <script>
    function toggleUserPlans() {
      const searchUserName = document.getElementById('searchUserName');
      const searchUserEmail = document.getElementById('searchUserEmail');
      const plansSection = document.getElementById('userPlansSection');
      const usersSection = document.getElementById('usersTableSection');
      const heading = document.getElementById('mainHeading');
      const subtext = document.getElementById('mainSubtext');
      const viewPlansBtn = document.getElementById('viewPlansBtn');
      const viewUsersBtn = document.getElementById('viewUsersBtn');

      const showingPlans = plansSection.style.display === 'block';

      if (showingPlans) {
        // Show Users
        searchUserName.style.display = 'block';
        searchUserEmail.style.display = 'block';
        plansSection.style.display = 'none';
        usersSection.style.display = 'block';
        heading.textContent = 'Users';
        subtext.textContent = 'Click "View" to see and edit user details.';
        viewPlansBtn.style.display = 'inline-block';
        viewUsersBtn.style.display = 'none';
      } else {
        // Show Plans
        searchUserName.style.display = 'none';
        searchUserEmail.style.display = 'none';
        plansSection.style.display = 'block';
        usersSection.style.display = 'none';
        heading.textContent = 'User Plans';
        subtext.textContent = 'Manage or assign plans to users below.';
        viewPlansBtn.style.display = 'none';
        viewUsersBtn.style.display = 'inline-block';
      }
    }

    function filterUsers() {
      const nameInput = document.getElementById("searchUserName").value.toLowerCase();
      const emailInput = document.getElementById("searchUserEmail").value.toLowerCase();
      const table = document.querySelector("#usersTableSection table");
      const rows = table.getElementsByTagName("tr");

      for (let i = 1; i < rows.length; i++) { // skip header
        const name = rows[i].getElementsByTagName("td")[1].innerText.toLowerCase();
        const email = rows[i].getElementsByTagName("td")[2].innerText.toLowerCase();

        if (name.includes(nameInput) && email.includes(emailInput)) {
          rows[i].style.display = "";
        } else {
          rows[i].style.display = "none";
        }
      }
    }



    function openPlanModal(id) {
      fetch(`get_user_plan.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
          document.getElementById("edit_user_plan_id").value = data.id;
          document.getElementById("edit_start_date").value = data.start_date;
          document.getElementById("edit_end_date").value = data.end_date;
          document.getElementById("edit_total_price").value = data.total_price;
          document.getElementById("planModalOverlay").style.display = "flex";
        });
    }

    function closePlanModal() {
      document.getElementById("planModalOverlay").style.display = "none";
    }

    function openAddPlanModal() {
      document.getElementById("addPlanModalOverlay").style.display = "flex";
    }

    function closeAddPlanModal() {
      document.getElementById("addPlanModalOverlay").style.display = "none";
    }

    // Form submit
    document.getElementById("editPlanForm").onsubmit = function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch("update_user_plan.php", {
        method: "POST",
        body: formData
      }).then(res => res.text()).then(msg => {
        alert(msg);
        closePlanModal();
        location.reload();
      });
    };

    document.getElementById("addPlanForm").onsubmit = function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch("add_user_plan.php", {
        method: "POST",
        body: formData
      }).then(res => res.text()).then(msg => {
        alert(msg);
        closeAddPlanModal();
        location.reload();
      });
    };


    function filterPlans() {
      const usernameInput = document.getElementById("searchUsername").value.toLowerCase();
      const planInput = document.getElementById("searchPlan").value.toLowerCase();
      const table = document.getElementById("userPlansTable");
      const rows = table.getElementsByTagName("tr");

      for (let i = 1; i < rows.length; i++) {
        const username = rows[i].getElementsByTagName("td")[0].innerText.toLowerCase();
        const plan = rows[i].getElementsByTagName("td")[1].innerText.toLowerCase();

        if (username.includes(usernameInput) && plan.includes(planInput)) {
          rows[i].style.display = "";
        } else {
          rows[i].style.display = "none";
        }
      }
    }

  </script>

  <script>
    let currentUserId = null;

    function openModal(id) {
      currentUserId = id;
      fetch(`get_user.php?id=${id}`)
        .then(res => res.json())
        .then(user => {
          const fields = Object.keys(user);
          const html = fields.map(key => `
            <div class="user-field">
              <div class="user-label">${formatLabel(key)}:</div>
              <div class="user-value" id="${key}_value">${user[key]}</div>
              <button class="edit-icon" onclick="editField('${key}')"><i class="fas fa-pen"></i></button>
            </div>
          `).join('');

          document.getElementById("userDetails").innerHTML = html;
          document.getElementById("modalOverlay").style.display = "flex";
        });
    }

    function closeModal() {
      document.getElementById("modalOverlay").style.display = "none";
    }

    function formatLabel(key) {
      return key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }

    function editField(key) {
      const currentValue = document.getElementById(`${key}_value`).innerText;
      document.getElementById(`${key}_value`).innerHTML =
        `<input type="text" class="edit-input" id="${key}_input" value="${currentValue}">`;
    }

    function saveChanges() {
      const inputs = document.querySelectorAll(".edit-input");
      let formData = new FormData();
      formData.append("id", currentUserId);

      inputs.forEach(input => {
        formData.append(input.id.replace('_input', ''), input.value);
      });

      fetch("update_user.php", {
        method: "POST",
        body: formData
      })
        .then(res => res.text())
        .then(response => {
          alert(response);
          closeModal();
          location.reload();
        });
    }
  </script>
  <script src="js/admin.js"></script>

</body>

</html>