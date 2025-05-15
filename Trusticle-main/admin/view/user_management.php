<?php include_once '../includes/header.php'; ?>

<div class="container">
    <!-- Sidebar is included in the header.php file -->
    <div class="content-area">
        <div class="page-header">
            <h1 class="page-title">User Management</h1>
        </div>
        
        <div class="action-bar">
            <div class="search-container">
                <input type="text" id="search-input" class="search-input" placeholder="Search by id, name or email...">
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
                <button id="add-user-btn" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New User
                </button>
                <button class="btn btn-primary">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Date Registered</th>
                        <th>Role</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>0</td>
                        <td>Rhea Manipon</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@rhtrm</td>
                        <td>01/24/2025</td>
                        <td data-role="user"><span class="role-badge role-user">User</span></td>
                        <td class="active-status">Active</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td>Jansdale Yusi</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@jsdle</td>
                        <td>01/24/2025</td>
                        <td data-role="admin"><span class="role-badge role-admin">Admin</span></td>
                        <td class="active-status">Active</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Regie Manipon</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@rtrmte</td>
                        <td>01/24/2025</td>
                        <td data-role="user"><span class="role-badge role-user">User</span></td>
                        <td class="inactive-status">Inactive</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Reiner Garcia</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@rtnrmchk</td>
                        <td>01/24/2025</td>
                        <td data-role="admin"><span class="role-badge role-admin">Admin</span></td>
                        <td class="active-status">Active</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Jackie Manipon</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@rtrmcnk</td>
                        <td>01/24/2025</td>
                        <td data-role="user"><span class="role-badge role-user">User</span></td>
                        <td class="active-status">Active</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Lovely Puno</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@rtrmcnk</td>
                        <td>01/24/2025</td>
                        <td data-role="admin"><span class="role-badge role-admin">Admin</span></td>
                        <td class="active-status">Active</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Josh Viray</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@rmncnk</td>
                        <td>01/24/2025</td>
                        <td data-role="admin"><span class="role-badge role-admin">Admin</span></td>
                        <td class="active-status">Active</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>Ani Garcia</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@rtncmntk</td>
                        <td>01/24/2025</td>
                        <td data-role="user"><span class="role-badge role-user">User</span></td>
                        <td class="active-status">Active</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>Reimer Manalang</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@rtngmchk</td>
                        <td>01/24/2025</td>
                        <td data-role="user"><span class="role-badge role-user">User</span></td>
                        <td class="inactive-status">Inactive</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>Rikki Ann</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@rtnsmchk</td>
                        <td>01/24/2025</td>
                        <td data-role="user"><span class="role-badge role-user">User</span></td>
                        <td class="inactive-status">Inactive</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>Reginajoyn Puno</td>
                        <td>maniponrhea@gmail.com</td>
                        <td>@rtntmntk</td>
                        <td>01/24/2025</td>
                        <td data-role="user"><span class="role-badge role-user">User</span></td>
                        <td class="active-status">Active</td>
                        <td>
                            <div class="action-icons">
                                <i class="fas fa-edit action-icon edit-icon"></i>
                                <i class="fas fa-trash action-icon delete-icon"></i>
                                <i class="fas fa-user-cog action-icon role-icon"></i>
                            </div>
                        </td>
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
            <a href="#">5</a>
            <a href="#" class="next">Next <i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
</div>

<!-- Modal for Adding Users -->
<div id="user-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New User</h2>
        <br/>
        <form id="user-form">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="first-name" placeholder="First Name">
                </div>
                <div class="form-group">
                    <input type="text" id="last-name" placeholder="Last Name">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="username" placeholder="Username">
                </div>
                <div class="form-group">
                    <input type="email" id="email" placeholder="Email">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="date" id="dob" placeholder="Date of Birth">
                </div>
                <div class="form-group">
                    <select id="role">
                        <option value="" disabled selected>Role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="password" id="password" placeholder="Password">
                </div>
                <div class="form-group">
                    <input type="password" id="confirm-password" placeholder="Confirm Password">
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="add-btn">Add User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Editing Users -->
<div id="edit-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit User</h2>
        <br/>
        <form id="edit-form">
            <input type="hidden" id="edit-user-id">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="edit-first-name" placeholder="First Name">
                </div>
                <div class="form-group">
                    <input type="text" id="edit-last-name" placeholder="Last Name">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="edit-username" placeholder="Username">
                </div>
                <div class="form-group">
                    <input type="email" id="edit-email" placeholder="Email">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="date" id="edit-dob" placeholder="Date of Birth">
                </div>
                <div class="form-group">
                    <select id="edit-active" class="active-select">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="password" id="edit-password" placeholder="Password" disabled>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="add-btn">Update User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Role Change -->
<div id="role-modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Change User Role</h2>
        <br/>
        <form id="role-form">
            <input type="hidden" id="role-user-id">
            <div class="form-group">
                <p id="role-user-name" class="user-info-text">Rhea Manipon</p>
            </div>
            <div class="form-group">
                <select id="role-change" class="role-select">
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="add-btn">Update Role</button>
            </div>
        </form>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>