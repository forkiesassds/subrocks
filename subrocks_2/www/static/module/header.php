<div class="www-header-mast">
    <a href="/"><img src="/static/img/fulptube.png" class="www-logo"></a>
    <span class="www-motto">
    Smashing Rocks &trade;
    </span>
    <div class="www-user-info">
        <?php if(!isset($_SESSION['siteusername'])) { ?>
            <strong><a style="border-left: 0px;" class="first" href="/sign_up">Sign Up</a></strong> 
            <a href="/quicklist">QuickList</a> (?) 
            <a href="/help">Help</a> 
            <a class="" href="/sign_in">Sign In</a>
        <?php } else { ?>
            <?php echo htmlspecialchars($_SESSION['siteusername']); ?>
        <?php } ?>
    </div>
    <br>
    <div class="www-header-list">
        <a class="www-header-item" href="/">Home</a>
        <a class="www-header-item" href="/videos">Videos</a>
        <a class="www-header-item" href="/channels">Channels</a>
        <a class="www-header-item" href="/community">Community</a>

        <form class="search-form" autocomplete="off" action="/search_query">
            <input name="q" class="search-box">
            <input type="submit" class="search-button" value="Search">
        </form>

        <a href="/upload_video">
            <button class="upload_button">
                Upload
            </button>
        </a>
    </div>
</div>
<?php 
    if(isset($_SESSION['siteusername']))
        $_base_utils->update_login_time($_SESSION['siteusername']); 
?>

<div class="alerts">
    <div class="alert" style="display: none;" id="editsuccess">
        Successfully updated your video!
    </div>
</div>