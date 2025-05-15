<?php include_once '../includes/header.php'; ?>

<div class="container">
    <!-- Sidebar is included in the header.php file -->
    <div class="content-area">
        <div class="page-header">
            <h1 class="page-title">Activity Log</h1>
        </div>

        <div class="action-bar activity-action-bar">
            <div class="search-container">
                <input type="text" id="search-input" class="search-input" placeholder="Search for user, activity...">
                <button class="search-icon"><i class="fas fa-search"></i></button>
            </div>
            <div class="actions-container">
                <div class="filter-container">
                    <button id="filter-btn" class="btn btn-outline">
                        <i class="fas fa-filter"></i> <span>All</span>
                    </button>
                    <div id="filter-dropdown" class="filter-dropdown">
                        <div class="filter-option" data-filter="all">All</div>
                        <div class="filter-option" data-filter="admin">Admin</div>
                        <div class="filter-option" data-filter="user">User</div>
                    </div>
                </div>
                <button class="btn btn-primary">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>

        <div class="table-container">
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Activity</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>0</td>
                        <td>Rhea Manipon</td>
                        <td>
                            <span class="role-badge role-user">User</span>
                        </td>
                        <td>Submitted article "Climate News"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>Jansdale Yusi</td>
                        <td>
                            <span class="role-badge role-admin">Admin</span>
                        </td>
                        <td>Approved article "Vaccine Update"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Rhea Manipon</td>
                        <td>
                            <span class="role-badge role-user">User</span>
                        </td>
                        <td>Edited article "Stock Market"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Anj Garcia</td>
                        <td>
                            <span class="role-badge role-admin">Admin</span>
                        </td>
                        <td>Deleted user "manjiron@mail.com"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Jackie Manipon</td>
                        <td>
                            <span class="role-badge role-user">User</span>
                        </td>
                        <td>Submitted article "Climate News"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Lovely Pune</td>
                        <td>
                            <span class="role-badge role-admin">Admin</span>
                        </td>
                        <td>Deleted user "manjiron@mail.com"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Josh Viray</td>
                        <td>
                            <span class="role-badge role-admin">Admin</span>
                        </td>
                        <td>Rejected article "Vaccine Update"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Anj Garcia</td>
                        <td>
                            <span class="role-badge role-user">User</span>
                        </td>
                        <td>Submitted article "Climate News"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>Reimer Manlalang</td>
                        <td>
                            <span class="role-badge role-user">User</span>
                        </td>
                        <td>Submitted article "Climate News"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>Rikki Ann</td>
                        <td>
                            <span class="role-badge role-user">User</span>
                        </td>
                        <td>Submitted article "Climate News"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>Reginajoy Purto</td>
                        <td>
                            <span class="role-badge role-user">User</span>
                        </td>
                        <td>Submitted article "Climate News"</td>
                        <td>02:30 PM</td>
                        <td><i class="fas fa-trash action-icon"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <a href="#" class="prev"><i class="fas fa-chevron-left"></i> Previous</a>
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#">4</a>
            <a href="#">...</a>
            <a href="#" class="next">Next <i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>