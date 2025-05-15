<?php include_once '../includes/header.php'; ?>

<div class="container">
  <!-- Sidebar is included in the header.php file -->
  <div class="content-area">
    <!-- Main Content Area -->
    <main class="main-content" id="main-content" tabindex="-1">
      <div class="settings-content">
        <!-- Edit Profile Tab -->
        <section class="tab-content active" id="edit-profile" role="tabpanel" aria-labelledby="tab-edit-profile">
          <h1>Settings</h1>
          <div class="content-card">
            <h2>Personal Information</h2>
            <div class="profile-section">
              <div class="profile-image-settings">
                <img src="https://images.unsplash.com/photo-1531746020798-e6953c6e8e04" alt="Profile picture of Rhea Manipon" />
              </div>
              <div class="profile-info">
                <h3>Rhea Manipon</h3>
                <p>@rhj.mnpn</p>
              </div>
            </div>
            <form class="settings-form" id="edit-profile-form" action="#" method="post" novalidate>
              <div class="form-group">
                <label for="first-name">First Name</label>
                <input id="first-name" name="first_name" type="text" value="Rhea" required />
              </div>
              <div class="form-group">
                <label for="last-name">Last Name</label>
                <input id="last-name" name="last_name" type="text" value="Manipon" required />
              </div>
              <div class="form-group">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" value="@rhj.mnpn" required />
              </div>
              <div class="form-group">
                <label for="dob">Date of Birth</label>
                <input id="dob" name="dob" type="date" value="1995-05-15" required />
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="rhea.manipon@example.com" required />
              </div>
              <button type="submit" class="save-button">SAVE CHANGES</button>
            </form>
          </div>
        </section>
        
        <!-- Account Security Tab -->
        <section class="tab-content" id="account-security" role="tabpanel" aria-labelledby="tab-account-security">
          <h1>Settings</h1>
          <div class="content-card">
            <h2>Account Security</h2>
            
            <nav class="security-tabs" role="tablist" aria-label="Account security tabs">
              <a
                href="#account-security/change-password"
                class="security-tab active"
                id="tab-change-password"
                role="tab"
                aria-selected="true"
                aria-controls="change-password"
                tabindex="0"
                >Change Password</a
              >
              <a
                href="#account-security/delete-account"
                class="security-tab"
                id="tab-delete-account"
                role="tab"
                aria-selected="false"
                aria-controls="delete-account"
                tabindex="-1"
                >Delete/Deactivate Account</a
              >
            </nav>
            
            <div class="security-content active" id="change-password" role="tabpanel" aria-labelledby="tab-change-password">
              <form class="settings-form" id="change-password-form" action="#" method="post" novalidate>
                <div class="form-group">
                  <label for="current-password">Current Password</label>
                  <input id="current-password" name="current_password" type="password" placeholder="Enter current password" required />
                </div>
                <div class="form-group">
                  <label for="new-password">New Password</label>
                  <input id="new-password" name="new_password" type="password" placeholder="Enter new password" required />
                </div>
                <div class="form-group">
                  <label for="confirm-password">Confirm Password</label>
                  <input
                    id="confirm-password"
                    name="confirm_password"
                    type="password"
                    placeholder="Confirm new password"
                    required
                  />
                </div>
                <button type="submit" class="save-button">SAVE CHANGES</button>
              </form>
            </div>
            
            <div class="security-content" id="delete-account" role="tabpanel" aria-labelledby="tab-delete-account" hidden>
              <div class="account-actions">
                <div class="action-section">
                  <h3>Deactivate Your Account</h3>
                  <p>
                    Deactivating your account will temporarily remove your profile and content from view.
                    You can reactivate your account at any time by logging back in.
                  </p>
                  <button type="button" class="action-button" id="deactivate-btn">Deactivate Account</button>
                </div>
                <div class="action-section">
                  <h3>Delete Your Account</h3>
                  <p>
                    Permanently delete your account and all associated data.
                    This action is irreversible and all your data will be permanently removed.
                  </p>
                  <button type="button" class="action-button" id="delete-btn">Delete Account</button>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>
</div>

<!-- Confirmation Modal -->
<dialog id="confirm-dialog" role="alertdialog" aria-modal="true" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-desc">
  <h2 id="confirm-dialog-title"></h2>
  <p id="confirm-dialog-desc"></p>
  <div class="dialog-buttons">
    <button id="confirm-yes" class="action-button">Yes</button>
    <button id="confirm-no" class="action-button">No</button>
  </div>
</dialog>

<!-- Info Modal (for messages replacing alert) -->
<dialog id="info-dialog" role="alertdialog" aria-modal="true" aria-labelledby="info-dialog-title" aria-describedby="info-dialog-desc">
  <h2 id="info-dialog-title"></h2>
  <p id="info-dialog-desc"></p>
  <div class="dialog-buttons">
    <button id="info-ok" class="action-button">OK</button>
  </div>
</dialog>

<script>
  $(document).ready(function() {
    // Handle hash and tabs
    function handleHash() {
      const hash = window.location.hash || '#edit-profile';
      const parts = hash.slice(1).split('/'); // remove #
      const mainTab = parts[0];
      const subTab = parts[1] || null;
      
      $('.tab-content').removeClass('active').attr('hidden', true);
      $('#' + mainTab).addClass('active').removeAttr('hidden');
      
      $('.dropdown-menu li').removeClass('active');
      $(`.dropdown-menu li[data-tab="${mainTab}"]`).addClass('active');
      
      if (mainTab === 'account-security') {
        if (subTab === 'delete-account' || subTab === 'delete') {
          changeSecurityTab('delete-account');
        } else {
          changeSecurityTab('change-password');
        }
      }
    }
    
    // Security tab switch
    function changeSecurityTab(tabId) {
      $('.security-tab').removeClass('active').attr({
        'aria-selected': 'false',
        tabindex: '-1'
      });
      $(`.security-tab[href="#account-security/${tabId}"]`).addClass('active').attr({
        'aria-selected': 'true',
        tabindex: '0'
      }).focus();
      
      $('.security-content').removeClass('active').attr('hidden', true);
      $('#' + tabId).addClass('active').removeAttr('hidden');
    }
    
    $(window).on('hashchange', handleHash);
    handleHash();
    
    // Confirmation dialog logic
    const $confirmDialog = $('#confirm-dialog');
    const $confirmTitle = $('#confirm-dialog-title');
    const $confirmDesc = $('#confirm-dialog-desc');
    const $confirmYes = $('#confirm-yes');
    const $confirmNo = $('#confirm-no');
    
    function showConfirmDialog(title, message, onConfirm) {
      $confirmTitle.text(title);
      $confirmDesc.text(message);
      
      function yesHandler() {
        onConfirm();
        $confirmDialog[0].close();
        cleanup();
      }
      
      function noHandler() {
        $confirmDialog[0].close();
        cleanup();
      }
      
      function cleanup() {
        $confirmYes.off('click', yesHandler);
        $confirmNo.off('click', noHandler);
      }
      
      $confirmYes.on('click', yesHandler);
      $confirmNo.on('click', noHandler);
      
      $confirmDialog[0].showModal();
    }
    
    // Info dialog for replacement of alert
    const $infoDialog = $('#info-dialog');
    const $infoTitle = $('#info-dialog-title');
    const $infoDesc = $('#info-dialog-desc');
    const $infoOk = $('#info-ok');
    
    function showInfoDialog(title, message) {
      $infoTitle.text(title);
      $infoDesc.text(message);
      
      function okHandler() {
        $infoDialog[0].close();
        $infoOk.off('click', okHandler);
      }
      
      $infoOk.on('click', okHandler);
      $infoDialog[0].showModal();
    }
    
    $('#deactivate-btn').on('click', function() {
      showConfirmDialog(
        'Confirm Account Deactivation',
        'Are you sure you want to deactivate your account? You can reactivate it later by logging in.',
        function() {
          // Show info modal instead of alert
          showInfoDialog('Account Deactivated', 'Your account has been deactivated (mock action).');
        }
      );
    });
    
    $('#delete-btn').on('click', function() {
      showConfirmDialog(
        'Confirm Account Deletion',
        'Are you sure you want to permanently delete your account? This action cannot be undone.',
        function() {
          // Show info modal instead of alert
          showInfoDialog('Account Deleted', 'Your account has been permanently deleted (mock action).');
        }
      );
    });
    
    // Trap focus inside dialogs while open
    function trapFocus($dialog) {
      $dialog.on('keydown', function(e) {
        if (e.key === 'Escape') {
          e.preventDefault();
          $dialog[0].close();
        }
        else if (e.key === 'Tab') {
          const focusable = $dialog.find('button:visible').toArray();
          const first = focusable[0];
          const last = focusable[focusable.length - 1];
          if (e.shiftKey) {
            if (document.activeElement === first) {
              e.preventDefault();
              last.focus();
            }
          } else {
            if (document.activeElement === last) {
              e.preventDefault();
              first.focus();
            }
          }
        }
      });
    }
    trapFocus($confirmDialog);
    trapFocus($infoDialog);
    
    // Form submission handler to prevent reload and keep current tab
    $('#change-password-form, #edit-profile-form').on('submit', function(e) {
      e.preventDefault();
      // You can add your AJAX save logic here
      showInfoDialog('Success', 'Changes saved (mock).');
    });
    
    // Handle security tab clicks
    $('.security-tab').on('click', function(e) {
      e.preventDefault();
      const href = $(this).attr('href');
      const tabId = href.split('/')[1];
      changeSecurityTab(tabId);
      
      // Update URL without page reload
      history.pushState(null, null, href);
    });
  });
</script>

<?php include_once '../includes/footer.php'; ?>