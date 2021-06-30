<?php require($_SERVER['DOCUMENT_ROOT'] . "/static/important/config.inc.php"); ?>
<?php require($_SERVER['DOCUMENT_ROOT'] . "/static/lib/new/base.php"); ?>
<?php require($_SERVER['DOCUMENT_ROOT'] . "/static/lib/new/fetch.php"); ?>
<?php require($_SERVER['DOCUMENT_ROOT'] . "/static/lib/new/insert.php"); ?>
<?php
    $_user_fetch_utils = new user_fetch_utils();
    $_video_fetch_utils = new video_fetch_utils();
    $_video_insert_utils = new video_insert_utils();
    $_user_insert_utils = new user_insert_utils();
    $_base_utils = new config_setup();
    
    $_base_utils->initialize_db_var($conn);
    $_video_fetch_utils->initialize_db_var($conn);
    $_user_fetch_utils->initialize_db_var($conn);
    $_user_insert_utils->initialize_db_var($conn);
    $_video_insert_utils->initialize_db_var($conn);

    $_user = $_user_fetch_utils->fetch_user_username($_GET['n']);
    $_user['subscribed'] = $_user_fetch_utils->if_subscribed(@$_SESSION['siteusername'], $_user['username']);
    $_user['dLinks'] = json_decode($_user['links']);
    $_user['subscribers'] = $_user_fetch_utils->fetch_subs_count($_user['username']);
    $_user['videos'] = $_user_fetch_utils->fetch_user_videos($_user['username']);
    $_user['favorites'] = $_user_fetch_utils->fetch_user_favorites($_user['username']);
    $_user['subscriptions'] = $_user_fetch_utils->fetch_subscriptions($_user['username']);

    $_base_utils->initialize_page_compass(htmlspecialchars($_user['username']));
    $_video_insert_utils->check_view_channel($_user['username'], @$_SESSION['siteusername']);

    if(empty($_user['bio'])) {
        $_user['bio'] = "No bio specified...";
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        if(!isset($_SESSION['siteusername'])){ $error = "you are not logged in"; goto skipcomment; }
        if(!$_POST['comment']){ $error = "your comment cannot be blank"; goto skipcomment; }
        if(strlen($_POST['comment']) > 501){ $error = "your comment must be shorter than 500 characters"; goto skipcomment; }
        if(!isset($_POST['g-recaptcha-response'])){ $error = "captcha validation failed"; goto skipcomment; }
        if(!$_user_insert_utils->validateCaptcha($config['recaptcha_secret'], $_POST['g-recaptcha-response'])) { $error = "captcha validation failed"; goto skipcomment; }

        $stmt = $conn->prepare("INSERT INTO `profile_comments` (toid, author, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $_user['username'], $_SESSION['siteusername'], $text);
        $text = htmlspecialchars($_POST['comment']);
        $stmt->execute();
        $stmt->close();
        skipcomment:
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>SubRocks - <?php echo $_base_utils->return_current_page(); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/static/css/new/www-core.css">
        <script src='https://www.google.com/recaptcha/api.js' async defer></script>
        <script>function onLogin(token){ document.getElementById('submitform').submit(); }</script>
        <style>
            .channel-box-top {
                background: #666;
                color: white;
                padding: 5px;
            }

            .sub_button {
                position: relative;
                bottom: 2px;
            }

            .channel-box-description {
                background: #e6e6e6;
                border: 1px solid #666;
                color: #666;
                padding: 5px;
            }

            .channel-box-no-bg {
                border: 1px solid #666;
                color: black;
                padding: 5px;
            }

            .channel-pfp {
                height: 88px;
                width: 88px;
                border-color: #666;
                border: 3px double #999;
            }

            .channel-stats {
                display: inline-block;
                vertical-align: top;
            }

            .channel-stats-minor {
                font-size: 11px;
            }
            
            .comment-pfp {
                width: 52px;
                height: 52px;
                border-color: #666;
                display: inline-block;
                border: 3px double #999;
            }
        </style>
    </head>
    <body>
        <div class="www-core-container">
            <?php require($_SERVER['DOCUMENT_ROOT'] . "/static/module/channel_header.php"); ?><br>
            <center>
                <a href="/user/<?php echo htmlspecialchars($_user['username']); ?>/videos">Videos</a> | 
                <a href="/user/<?php echo htmlspecialchars($_user['username']); ?>/favorites">Favorites</a> |
                <a href="/user/<?php echo htmlspecialchars($_user['username']); ?>/playlists">Playlists</a> |
                <a href="/user/<?php echo htmlspecialchars($_user['username']); ?>/groups">Groups</a> |
                <a href="/user/<?php echo htmlspecialchars($_user['username']); ?>/friends">Friends</a> |  
                <a href="/user/<?php echo htmlspecialchars($_user['username']); ?>/subscribers">Subscribers</a> |   
                <a href="/user/<?php echo htmlspecialchars($_user['username']); ?>/subscriptions">Subscriptions</a>
            </center><br>
            <div class="www-channel-left">
                <div class="channel-box-profle">
                    <div class="channel-box-top">
                        <h3 style="display: inline-block;"><?php echo htmlspecialchars($_user['username']); ?>'s Channel</h3>

                        <a href="/get/<?php if($_user['subscribed'] == true) { ?>un<?php } ?>subscribe?n=<?php echo htmlspecialchars($_user['username']); ?>">
                            <button style="margin: 0px;" class="sub_button"><?php if($_user['subscribed'] == true) { ?>Unsubscribe<?php } else { ?>Subscribe<?php } ?></button>
                        </a>
                    </div>
                    <div class="channel-box-description">
                        <img class="channel-pfp" src="/dynamic/pfp/<?php echo $_user['pfp']; ?>">
                        <span class="channel-stats">
                            <h3><?php echo htmlspecialchars($_user['username']); ?></h3>
                            <span class="channel-stats-minor">
                                Joined: <b><?php echo date("M d, Y", strtotime($_user['created'])); ?></b><br>
                                Last Sign In: <b><?php echo date("M d, Y", strtotime($_user['lastlogin'])); ?></b><br>
                                Videos Watched: <b><?php echo $_video_fetch_utils->fetch_history_ammount($_user['username']); ?></b><br>
                                Subscribers: <b><?php echo $_user_fetch_utils->fetch_subs_count($_user['username']); ?></b><br>
                                Channels Views: <b><?php echo $_user_fetch_utils->get_channel_views($_user['username']); ?></b><br>
                            </span>
                        </span>
                    </div>
                </div><br>
                <div class="channel-box-profle">
                    <div class="channel-box-top" style="height: 12px;">
                        <h3 style="display: inline-block;">Contact with <?php echo htmlspecialchars($_user['username']); ?></h3>
                    </div>
                    <div class="channel-box-no-bg">
                        <div style="display: inline-block;width:116px;height:95px;"></div>
                        <span style="display: inline-block;vertical-align: top;line-height: 30px;line-height: 21px;font-size: 11px;">
                            <img src="/static/img/message.png" style="vertical-align: middle;"> <a href="/inbox/send?to=<?php echo htmlspecialchars($_user['username']); ?>">Send Message</a><br>
                            <img src="/static/img/comment.png" style="vertical-align: middle;"> <a href="#">Add Contact</a><br>
                            <img src="/static/img/share.png" style="vertical-align: middle;"> <a href="#">Share Channel</a>
                        </span><br>
                        <center><a href="/user/<?php echo htmlspecialchars($_user['username']); ?>">http://subrocks/user/<?php echo htmlspecialchars($_user['username']); ?></a></center>

                    </div>
                </div><br>

                <div class="channel-box-profle">
                    <div class="channel-box-top" style="height: 12px;">
                        <h3 style="display: inline-block;">Recent Activity</h3>
                    </div>
                    <div class="channel-box-no-bg" style="color: #666;">
                        <?php
                            $stmt = $conn->prepare("SELECT rid, title, thumbnail, duration, title, author, publish, description FROM videos WHERE author = ? ORDER BY id DESC LIMIT 5");
                            $stmt->bind_param("s", $_user['username']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while($video = $result->fetch_assoc()) {
                        ?>
                            <img style="vertical-align: middle;" src="/static/img/upload.png"> <b><?php echo htmlspecialchars($video['author']); ?> uploaded a video</b> (<?php echo $_video_fetch_utils->time_elapsed_string($video['publish']); ?>)
                            <div class="video-item-watch" style="margin-left: 20px;">
                                <div class="thumbnail" style="
                                    background-image: url(/dynamic/thumbs/<?php echo $video['thumbnail']; ?>), url('/dynamic/thumbs/default.png');"><span class="timestamp"><?php echo $_video_fetch_utils->timestamp($video['duration']); ?></span></div>
                                
                                <div class="video-info-watch" style="width: 170px;">
                                    <a href="/watch?v=<?php echo $video['rid']; ?>"><b><?php echo htmlspecialchars($video['title']); ?></b></a><br>
                                    <span class="video-info-small-wide">
                                        <span class="video-views"><?php echo $_video_fetch_utils->fetch_video_views($video['rid']); ?> views</span><br>
                                        <a style="padding-left: 0px;" class="video-author-wide" href="/user/<?php echo htmlspecialchars($video['author']); ?>"><?php echo htmlspecialchars($video['author']); ?></a>
                                    </span>
                                </div>
                                
                            </div>
                            <hr class="thin-line">
                        <?php } if($result->num_rows == 0)  { echo "This user has not been doing anything recently."; } ?>
                    </div>
                </div><br>

                <?php if($_user['subscriptions'] != 0) { ?>
                <div class="channel-box-profle">
                    <div class="channel-box-top">
                        <h3 style="display: inline-block;">Subscriptions (<?php echo $_user['subscriptions']; ?>)</h3>
                    </div>
                    <div class="channel-box-no-bg">
                        <?php
                            $stmt = $conn->prepare("SELECT reciever FROM subscribers WHERE sender = ? ORDER BY id DESC LIMIT 8");
                            $stmt->bind_param("s", $_user['username']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while($subscriber = $result->fetch_assoc()) {
                                if($_user_fetch_utils->user_exists($subscriber['reciever'])) {
                        ?>

                                <div class="grid-item" style="width: 66px;">
                                    <img class="channel-pfp" style="width: 58px; height: 58px;" src="/dynamic/pfp/<?php echo $_user_fetch_utils->fetch_user_pfp($subscriber['reciever']); ?>"><br>
                                    <a style="font-size: 10px;text-decoration: none;" href="/user/<?php echo htmlspecialchars($subscriber['reciever']); ?>"><?php echo htmlspecialchars($subscriber['reciever']); ?></a>
                                </div>
                        <?php } } ?>
                    </div>
                </div><br>
                <?php } ?>
            </div>
            <div class="www-channel-right">
                <?php if($_user['videos'] != 0) { ?>
                <div class="channel-box-profle">
                    <div class="channel-box-top" style="height: 12px;">
                        <h3 style="display: inline-block;">Videos (<?php echo $_user['videos']; ?>)</h3>
                    </div>
                    <div class="channel-box-no-bg">
                        <?php
                            $stmt = $conn->prepare("SELECT rid, title, thumbnail, duration, title, author, publish, description FROM videos WHERE author = ? AND visibility = 'v' ORDER BY id DESC LIMIT 8");
                            $stmt->bind_param("s", $_user['username']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while($video = $result->fetch_assoc()) {
                        ?>

                                <div class="grid-item" style="">
                                    <img class="thumbnail" src="/dynamic/thumbs/<?php echo htmlspecialchars($video['thumbnail']); ?>">
                                    <div class="video-info-grid">
                                        <a href="/watch?v=<?php echo $video['rid']; ?>"><?php echo htmlspecialchars($video['title']); ?></a><br>
                                        <span class="video-info-small">
                                            <span class="video-views"><?php echo $_video_fetch_utils->fetch_video_views($video['rid']); ?> views</span><br>
                                            <a href="/user/<?php echo htmlspecialchars($video['author']); ?>"><?php echo htmlspecialchars($video['author']); ?></a>
                                        </span>
                                    </div>
                                </div>
                        <?php } ?>
                    </div>
                </div><br>
                <?php } ?>

                <?php if($_user['favorites'] != 0) { ?>
                <div class="channel-box-profle">
                    <div class="channel-box-top" style="height: 12px;">
                        <h3 style="display: inline-block;">Favorites (<?php echo $_user['favorites']; ?>)</h3>
                    </div>
                    <div class="channel-box-no-bg">
                        <?php
                            $stmt = $conn->prepare("SELECT reciever FROM favorite_video WHERE sender = ? ORDER BY id DESC LIMIT 4");
                            $stmt->bind_param("s", $_user['username']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while($video = $result->fetch_assoc()) {
                                $video = $_video_fetch_utils->fetch_video_rid($video['reciever']);
                        ?>

                                <div class="grid-item" style="">
                                    <img class="thumbnail" src="/dynamic/thumbs/<?php echo htmlspecialchars($video['thumbnail']); ?>">
                                    <div class="video-info-grid">
                                        <a href="/watch?v=<?php echo $video['rid']; ?>"><?php echo htmlspecialchars($video['title']); ?></a><br>
                                        <span class="video-info-small">
                                            <span class="video-views"><?php echo $_video_fetch_utils->fetch_video_views($video['rid']); ?> views</span><br>
                                            <a href="/user/<?php echo htmlspecialchars($video['author']); ?>"><?php echo htmlspecialchars($video['author']); ?></a>
                                        </span>
                                    </div>
                                </div>
                        <?php } ?>
                    </div>
                </div><br>
                <?php } ?>

                <?php if($_user['subscribers'] != 0) { ?>
                <div class="channel-box-profle">
                    <div class="channel-box-top" style="height: 12px;">
                        <h3 style="display: inline-block;">Subscribers (<?php echo $_user['subscribers']; ?>)</h3>
                    </div>
                    <div class="channel-box-no-bg">
                        <?php
                            $stmt = $conn->prepare("SELECT sender FROM subscribers WHERE reciever = ? ORDER BY id DESC LIMIT 8");
                            $stmt->bind_param("s", $_user['username']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while($subscriber = $result->fetch_assoc()) {
                        ?>

                                <div class="grid-item" style="">
                                    <img class="channel-pfp" src="/dynamic/pfp/<?php echo $_user_fetch_utils->fetch_user_pfp($subscriber['sender']); ?>"><br>
                                    <a style="font-size: 10px;text-decoration: none;" href="/user/<?php echo htmlspecialchars($subscriber['sender']); ?>"><?php echo htmlspecialchars($subscriber['sender']); ?></a>
                                </div>
                        <?php } ?>
                    </div>
                </div><br>
                <?php } ?>
                <?php 
                    $stmt = $conn->prepare("SELECT * FROM profile_comments WHERE toid = ? ORDER BY id DESC");
                    $stmt->bind_param("s", $_user['username']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                ?>
                <div class="channel-box-profle">
                    <div class="channel-box-top" style="height: 12px;">
                        <h3 style="display: inline-block;">Channel Comments (<?php echo $result->num_rows; ?>)</h3>
                    </div>
                    <div class="channel-box-no-bg">
                        <?php if(!isset($_SESSION['siteusername'])) { ?>
                            <div class="comment-alert">
                                <a href="/sign_in">Sign In</a> or <a href="/create_account">Sign Up</a> now to post a comment!
                            </div>
                        <?php } else if($_video['commenting'] == "d") { ?>
                            <div class="comment-alert">
                                This video has commenting disabled!
                            </div>
                        <?php } else { ?>
                            <form method="post" action="" id="submitform">
                                <?php echo $error . "<br>"; ?>
                                <small><small style="font-size: 11px; color: #555;">This site is protected by reCAPTCHA and the Google
                                    <a class="grey-link" href="https://policies.google.com/privacy">Privacy Policy</a> and
                                    <a class="grey-link" href="https://policies.google.com/terms">Terms of Service</a> apply.</small></small><br>
                            
                                    <textarea 
                                        onkeyup="textCounter(this,'counter',500);" 
                                        class="comment-textbox" cols="32" id="com" style="width: 98%;"
                                        placeholder="Leave a nice comment on this channel" name="comment"></textarea><br><br> 
                                    <input disabled class="characters-remaining" maxlength="3" size="3" value="500" id="counter"> <?php if(!isset($cLang)) { ?> characters remaining <?php } else { echo $cLang['charremaining']; } ?> 
                                    <input type="submit" value="Post" class="g-recaptcha" data-sitekey="<?php echo $config['recaptcha_sitekey']; ?>" data-callback="onLogin">
                                    <script>
                                    function textCounter(field,field2,maxlimit) {
                                        var countfield = document.getElementById(field2);
                                        if ( field.value.length > maxlimit ) {
                                            field.value = field.value.substring( 0, maxlimit );
                                            return false;
                                        } else {
                                            countfield.value = maxlimit - field.value.length;
                                        }
                                        }
                                    </script>
                            </form>
                        <?php } ?><br>
                        
                        <?php while($comment = $result->fetch_assoc()) {  ?>
                        <hr class="thin-line">
                        <div class="comment-watch">
                            <img class="comment-pfp" src="/dynamic/pfp/<?php echo $_user_fetch_utils->fetch_user_pfp($comment['author']); ?>">
                            <span  style="display: inline-block; vertical-align: top;">
                                <span class="comment-info" style="display: inline-block;">
                                    <b><a style="text-decoration: none;" href="/user/<?php echo htmlspecialchars($comment['author']); ?>">
                                        <?php echo htmlspecialchars($comment['author']); ?> 
                                    </a></b> 
                                    <span style="color: #666;">(<?php echo $_video_fetch_utils->time_elapsed_string($comment['date']); ?>)</span>
                                </span><br>
                                <span class="comment-text" style="display: inline-block;">
                                    <?php echo $_video_fetch_utils->parseTextDescription($comment['comment']); ?>
                                </span>
                            </span>

                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="www-core-container">
        <?php require($_SERVER['DOCUMENT_ROOT'] . "/static/module/footer.php"); ?>
        </div>

    </body>
</html>