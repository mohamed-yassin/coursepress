<?php
$page = $_GET['page'];
$s = (isset($_GET['s']) ? $_GET['s'] : '');

if (isset($_POST['action']) && isset($_POST['users']) && current_user_can('administrator')) {
    check_admin_referer('bulk-instructors');

    $action = $_POST['action'];
    foreach ($_POST['users'] as $user_value) {

        if (is_numeric($user_value)) {

            $instructor_id = (int) $user_value;
            $instructor = new Instructor($instructor_id);

            switch (addslashes($action)) {
                case 'delete':
                    $instructor->delete_instructor();
                    $message = __('Selected instructors has been removed successfully.', 'cp');
                    break;

                case 'unassign':
                    $instructor->unassign_from_all_courses();
                    $message = __('Selected instructors has been unassigned from all courses successfully.', 'cp');
                    break;
            }
        }
    }
}

if (isset($_GET['page_num'])) {
    $page_num = $_GET['page_num'];
} else {
    $page_num = 1;
}

if (isset($_GET['s'])) {
    $usersearch = $_GET['s'];
} else {
    $usersearch = '';
}

if (isset($_GET['instructor_id']) && is_numeric($_GET['instructor_id'])) {
    $instructor = new Instructor($_GET['instructor_id']);
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['instructor_id']) && is_numeric($_GET['instructor_id'])) {
    $instructor->delete_instructor();
    $message = __('Selected instructor has been removed successfully.', 'cp');
}

if (isset($_GET['action']) && ($_GET['action'] == 'edit' || $_GET['action'] == 'view') && isset($_GET['instructor_id']) && is_numeric($_GET['instructor_id'])) {
    include('instructors-profile.php');
} else {

    // Query the users
    $wp_user_search = new Instructor_Search($usersearch, $page_num);
    ?>

    <div class="wrap nosubsub">

        <div class="icon32 " id="icon-users"><br></div>
        <h2><?php _e('Instructors', 'cp'); ?><?php if (current_user_can('administrator')) { ?><a class="add-new-h2" href="user-new.php"><?php _e('Add New', 'cp'); ?></a><?php } ?></h2>

        <?php
        if (isset($message)) {
            ?>
            <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
            <?php
        }
        ?>

        <div class="tablenav">

            <div class="alignright actions new-actions">
                <form method="get" action="?page=<?php echo esc_attr($page); ?>" class="search-form">
                    <p class="search-box">
                        <input type='hidden' name='page' value='<?php echo esc_attr($page); ?>' />
                        <label class="screen-reader-text"><?php _e('Search Instructors', 'cp'); ?>:</label>
                        <input type="text" value="<?php echo esc_attr(isset($s) ? $s : ''); ?>" name="s">
                        <input type="submit" class="button" value="<?php _e('Search Instructors', 'cp'); ?>">
                    </p>
                </form>
            </div>

            <form method="post" action="?page=<?php echo esc_attr($page); ?>" id="posts-filter">

                <div class="alignleft actions">
                    <?php if (current_user_can('administrator')) { ?>
                        <select name="action">
                            <option selected="selected" value=""><?php _e('Bulk Actions', 'cp'); ?></option>
                            <option value="delete"><?php _e('Delete', 'cp'); ?></option>
                            <option value="unassign"><?php _e('Unassign from all courses', 'cp'); ?></option>
                        </select>
                        <input type="submit" class="button-secondary action" id="doaction" name="doaction" value="<?php _e('Apply', 'cp'); ?>" />
                    <?php } ?>
                </div>


                <br class="clear">

                </div><!--/tablenav-->


                <?php
                wp_nonce_field('bulk-instructors');

                $columns = array(
                    "ID" => __('ID', 'cp'),
                    "user_firstname" => __('First Name', 'cp'),
                    "user_lastname" => __('Surname', 'cp'),
                    "registration_date" => __('Registered', 'cp'),
                    "courses" => __('Courses', 'cp'),
                    "edit" => __('Profile', 'cp'),
                );



                $col_sizes = array(
                    '5', '15', '15', '20', '15', '15'
                );

                if (current_user_can('administrator')) {
                    $columns["delete"] = __('Delete', 'cp');
                    $col_sizes[] = '6';
                }
                ?>

                <table cellspacing="0" class="widefat fixed shadow-table unit-control-buttons">
                    <thead>
                        <tr>
                            <th class="manage-column column-cb check-column" id="cb" scope="col" style="width:5%;">
                                <input type="checkbox">
                            </th>
                            <?php
                            $n = 0;
                            foreach ($columns as $key => $col) {
                                ?>
                                <th style="" class="manage-column column-<?php echo $key; ?>" width="<?php echo $col_sizes[$n] . '%'; ?>" id="<?php echo $key; ?>" scope="col"><?php echo $col; ?></th>
                                <?php
                                $n++;
                            }
                            ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $style = '';

                        foreach ($wp_user_search->get_results() as $user) {

                            $user_object = new Instructor($user->ID);
                            $roles = $user_object->roles;
                            $role = array_shift($roles);

                            $style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
                            ?>
                            <tr id='user-<?php echo $user_object->ID; ?>' <?php echo $style; ?>>
                                <th scope='row' class='check-column'>
                                    <input type='checkbox' name='users[]' id='user_<?php echo $user_object->ID; ?>' value='<?php echo $user_object->ID; ?>' />
                                </th>
                                <td <?php echo $style; ?>><?php echo $user_object->ID; ?></td>
                                <td <?php echo $style; ?>><?php echo $user_object->first_name; ?></td>
                                <td <?php echo $style; ?>><?php echo $user_object->last_name; ?></td>
                                <td <?php echo $style; ?>><?php echo $user_object->user_registered; ?></td>
                                <td <?php echo $style; ?>><?php echo $user_object->courses_number; ?></td>
                                <td <?php echo $style; ?> style="padding-top:9px; padding-right:15px;"><a href="?page=instructors&action=view&instructor_id=<?php echo $user_object->ID; ?>">
                                        <i class="fa fa-user cp-move-icon remove-btn"></i>
                                    </a>
                                </td>
                                <?php if (current_user_can('administrator')) { ?>
                                    <td <?php echo $style; ?> style="padding-top:13px;"><a href="?page=instructors&action=delete&instructor_id=<?php echo $user_object->ID; ?>" onclick="return removeInstructors();">
                                            <i class="fa fa-times-circle cp-move-icon remove-btn"></i>
                                        </a></td>
                                <?php } ?>
                            </tr>
                            <?php
                        }
                        ?>

                        <?php
                        if (count($wp_user_search->get_results()) == 0) {
                            ?>
                            <tr><td colspan="8"><div class="zero"><?php _e('No instructors found.', 'cp'); ?></div></td></tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>

                <div class="tablenav">
                    <div class="tablenav-pages"><?php $wp_user_search->page_links(); ?></div>
                </div><!--/tablenav-->

            </form>

        </div>

    <?php } ?>