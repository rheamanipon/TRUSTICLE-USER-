<!-- Post Article Modal -->
<div class="modal-overlay hidden" id="modalOverlay">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Post an Article</h2>
            <button class="close-button" id="closeModalBtn">&times;</button>
        </div>
        
        <form class="modal-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-row">
                <input type="text" class="form-input" name="title" placeholder="Title">
            </div>
            
            <div class="form-row">
                <select class="form-select" name="category">
                    <option value="" disabled selected>Category</option>
                    <option value="technology">Technology</option>
                    <option value="business">Business</option>
                    <option value="science">Science</option>
                    <option value="health">Health</option>
                </select>
            </div>
            
            <div class="form-row">
                <input type="text" class="form-input" name="source_url" placeholder="Source URL">
            </div>
            
            <div class="form-row">
                <input type="text" class="form-input" name="date" placeholder="Select publication date" readonly>
            </div>
            
            <div class="form-row">
                <textarea class="form-textarea" name="content" placeholder="put your content here..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="cancel-button" id="cancelBtn">CANCEL</button>
                <button type="submit" class="submit-button" name="submit_article">SUBMIT</button>
            </div>
        </form>
    </div>
</div>

<!-- Article View Modal -->
<div class="modal-overlay hidden" id="articleModalOverlay">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Article Details</h2>
            <button class="close-button" id="closeArticleModalBtn">&times;</button>
        </div>
        
        <div class="article-modal-content">
            <h3 id="articleModalTitle" class="article-title"></h3>
            <div class="article-meta">
                <div class="article-category" id="articleModalCategory"></div>
                <div class="article-source" id="articleModalSource"></div>
                <div class="article-date" id="articleModalDate"></div>
            </div>
            <div class="article-content" id="articleModalContent"></div>
        </div>
        
        <div class="form-actions">
            <button type="button" class="submit-button" id="closeArticleBtn">CLOSE</button>
        </div>
    </div>
</div>