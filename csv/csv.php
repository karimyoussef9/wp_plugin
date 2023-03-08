<?php
/**
 * Plugin Name: CSV Upload and Replace
 * Description: Uploads a CSV file and replaces [@] in the content with the CSV data.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://your-website.com/
 */
// put this plugin in new tab in wordpress admin area called CSV Upload and Replace
// create a new table in the database called csv_data_table
use function PHPSTORM_META\type;

// add a new menu item in wordpress admin area called CSV Upload and Replace



// Add a settings page to the WordPress admin area
add_action( 'admin_menu', 'csv_upload_replace_add_menu' );
function csv_upload_replace_add_menu() {
    // add menu page to the admin area
add_menu_page( 'CSV Upload and Replace Settings', 'CSV Upload and Replace', 'manage_options', 'csv-upload-replace', 'csv_upload_replace_settings_page' );
// add csv_get_data_settings_page as a submenu page to the csv_upload_replace_settings_page
add_submenu_page( 'csv-upload-replace', 'CSV Get Data', 'CSV Get Data', 'manage_options', 'csv-get-data', 'csv_get_data_settings_page' );
}   
// create table in database called csv_data_table with 4 columns title, locality, address, phone
global $wpdb;
$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'csv_data_table';
$sql = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    title text NOT NULL,
    locality text NOT NULL,
    address text NOT NULL,
    phone text NOT NULL,
   cordi1 decimal(11,8) NOT NULL,
    cordi2 decimal(11,8) NOT NULL,
    UNIQUE KEY id (id)
) $charset_collate;";
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
dbDelta( $sql );

// Display the settings page form
function csv_upload_replace_settings_page() {
    if (isset($_POST['submit'])) {
        $file = $_FILES['csv-file'];
        if ($file['type'] !== 'text/csv') {
            echo '<div class="error"><p>Invalid file type. Please upload a CSV file.</p></div>';
        } else {
        // get the csv file title column data
        $csv_title = array_map('str_getcsv', file($file['tmp_name']));
        $csv_title = array_column($csv_title, 0);
        // get the csv file locality column data
        $csv_locality = array_map('str_getcsv', file($file['tmp_name']));
        $csv_locality = array_column($csv_locality, 3);
        // get the csv file third address data
        $csv_address = array_map('str_getcsv', file($file['tmp_name']));
        $csv_address = array_column($csv_address, 1);
        // get the csv file fourth phone data
        $csv_phone = array_map('str_getcsv', file($file['tmp_name']));
        $csv_phone = array_column($csv_phone, 8);
        // cordin1
        $csv_cord1 = array_map('str_getcsv', file($file['tmp_name']));
        $csv_cord1 = array_column($csv_cord1, 9);
        // cordin2 
        $csv_cord2 = array_map('str_getcsv', file($file['tmp_name']));
        $csv_cord2 = array_column($csv_cord2, 10);
        // put each row title, locality, address, phone in an array
        $csv_data = array();
        for ($i = 0; $i < count($csv_title); $i++) {
            $csv_data[$i] = array(
                'title' => $csv_title[$i],
                'locality' => $csv_locality[$i],
                'address' => $csv_address[$i],
                'phone' => $csv_phone[$i],
                'cordi1' => $csv_cord1[$i],
                'cordi2' => $csv_cord2[$i]
            );
            
         
        }
        //remove the first index of the array because it is the header
        array_shift($csv_data);
        // print_r($csv_data);
        // insert the data into the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'csv_data_table';
        foreach ($csv_data as $data) {
            $wpdb->insert(
                $table_name,
                array(
                    'title' => $data['title'],
                    'locality' => $data['locality'],
                    'address' => $data['address'],
                    'phone' => $data['phone'],
                    'cordi1' => $data['cordi1'],
                    'cordi2' => $data['cordi2']
                )
            );


        }
        }
    }else{
    } 

    // Display the form
    ?>
    <div class="wrap">
        <h2>CSV Upload and Replace Settings</h2>
        <form method="post" enctype="multipart/form-data">
            <label for="csv-file">CSV File:</label>
            <input type="file" name="csv-file" id="csv-file">
            <input type="submit" name="submit" value="Upload">
        </form>
    </div>
    <?php
  
}

function csv_get_data_settings_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'csv_data_table';
    //add search to the table to search for the data by locality
    echo '<form method="post" action="?page=csv-get-data">';
    echo '<input type="text" name="search" placeholder="Search by locality" style="width:50%; margin:15px; margin-left:0px;">';
    echo '<input type="submit" name="submit" value="Search">';
    echo '</form>';
    if (isset($_POST['submit'])) {
        $search = $_POST['search'];
        // condition check if there is result for the search
        if ($wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE locality = '$search'") == 0) {
        } else {
            $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE locality = '$search'" );
            echo '<table class="wp-list-table widefat fixed striped posts" >';
            echo '<thead>';
            echo '<tr>';
            echo '<th class="manage-column column-title column-primary">Title</th>';
            echo '<th class="manage-column column-title column-primary">Locality</th>';
            echo '<th class="manage-column column-title column-primary">Address</th>';
            echo '<th class="manage-column column-title column-primary">Phone</th>';
            echo '<th class="manage-column column-title column-primary">Actions</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ($results as $result) {
                echo '<tr>';
                echo '<td class="manage-column column-title column-primary">' . $result->title . '</td>';
                echo '<td class="manage-column column-title column-primary">' . $result->locality . '</td>';
                echo '<td class="manage-column column-title column-primary">' . $result->address . '</td>';
                echo '<td class="manage-column column-title column-primary">' . $result->phone . '</td>';
                echo '<td class="manage-column column-title column-primary"><a href="?page=csv-get-data&action=edit&id=' . $result->id . '">Edit</a> <a href="?page=csv-get-data&action=delete&id=' . $result->id . '">Delete</a></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';    
        }

       
    }
    // display the data from the database in the admin area and add action to delete and edit the data
$results = $wpdb->get_results( "SELECT * FROM $table_name" );
echo '<table class="wp-list-table widefat fixed striped posts">';
echo '<thead>';
echo '<tr>';
echo '<th class="manage-column column-title column-primary">Title</th>';
echo '<th class="manage-column column-title column-primary">Locality</th>';
echo '<th class="manage-column column-title column-primary">Address</th>';
echo '<th class="manage-column column-title column-primary">Phone</th>';
echo '<th class="manage-column column-title column-primary">Actions</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
foreach ($results as $result) {
    echo '<tr>';
    echo '<td class="manage-column column-title column-primary">' . $result->title . '</td>';
    echo '<td class="manage-column column-title column-primary">' . $result->locality . '</td>';
    echo '<td class="manage-column column-title column-primary">' . $result->address . '</td>';
    echo '<td class="manage-column column-title column-primary">' . $result->phone . '</td>';
    echo '<td class="manage-column column-title column-primary">';
    echo '<a href="?page=csv-get-data&action=edit&id=' . $result->id . '">Edit</a> | ';
    echo '<a href="?page=csv-get-data&action=delete&id=' . $result->id . '">Delete</a>';
    echo '</td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';

// if the action is delete then delete the data from the database
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    
    $wpdb->delete( $table_name, array( 'id' => $id ) );
}
// if the action is edit display popup to edit the data
if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $id = $_GET['id'];
    $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE id = '$id'" );
    foreach ($results as $result) {
        $title = $result->title;
        $locality = $result->locality;
        $address = $result->address;
        $phone = $result->phone;
        $cord1 = $result->cordi1;
        $cord2 = $result->cordi2;
    }
    ?>
    <div class="" style="position:fixed; height: 50vh; height: 100%; top: 10%; width: 90%; background-color:#f6f7f7; margin-top: 2%;" >
        <h2 style="color: black;">Edit Data</h2>
        <form method="post" style="color: black; display: flex; justify-content: space-between; flex-wrap: wrap; " action="?page=csv-get-data&action=update&id=<?php echo $id; ?>">
            <div class="box1" style="flex-basis: 50%;">
                <div class="box" style="padding-bottom: 15px;">
                    <label for="title" >Title:</label>
                    <br>
                    <input type="text" name="title" style=" margin-top: 5px; width: 80%;" id="title" value="<?php echo $title; ?>">
                </div>
                <div class="box" style="padding-bottom: 15px;">
                    <label for="locality" >Locality:</label>
                    <br>
                    <input type="text" name="locality" style=" margin-top: 5px; width: 80%;" id="locality" value="<?php echo $locality; ?>">
                </div>
                <div class="box" style="padding-bottom: 15px;">
                    <label for="address" >Address:</label>
                    <br>
                    <input type="text" name="address" style=" margin-top: 5px; width: 80%;" id="address" value="<?php echo $address; ?>">
                </div>
            </div>
            <div class="box2" style="flex-basis: 50%;">
                <div class="box" style="padding-bottom: 15px;">
                    <label for="phone" >Phone:</label>
                    <br>
                    <input type="text" name="phone" style=" margin-top: 5px; width: 80%;" id="phone" value="<?php echo $phone; ?>">
                </div>
                <div class="box" style="padding-bottom: 15px;">
                    <label for="cordi1" >Cordi1:</label>
                    <br>
                    <input type="text" name="cordi1" style=" margin-top: 5px; width: 80%;" id="cordi1" value="<?php echo $cord1; ?>">
                </div>
                <div class="box" style="padding-bottom: 15px;">
                    <label for="cordi2" >Cordi2:</label>
                    <br>
                    <input type="text" name="cordi2" style=" margin-top: 5px; width: 80%;" id="cordi2" value="<?php echo $cord2; ?>">
                </div>
            </div>
            <br>
            <input type="submit" name="submit" value="Update" style="background-color: rgb(13, 157, 235) ; border: none; padding: 10px 30px; color: white; border-radius: 5px;" >
        </form>
    </div>
    <?php
    // if the action is update then update the data in the database and if there is a change in the data then update the data

} elseif (isset($_GET['action']) && $_GET['action'] === 'update') {
    $id = $_GET['id'];
    $title = $_POST['title'];
    $locality = $_POST['locality'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $cord1 = $_POST['cordi1'];
    $cord2 = $_POST['cordi2'];
    $wpdb->update( $table_name, array(
        'title' => $title,
        'locality' => $locality,
        'address' => $address,
        'phone' => $phone,
        'cordi1' => $cord1,
        'cordi2' => $cord2,
    ), array( 'id' => $id ) );
}

}

// sortcode to replace the content with the data from the database
function replace_shortcode( ) {
    $title = get_the_title();
    global $wpdb;
    $table_name = $wpdb->prefix . 'csv_data_table';
    $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE Locality = '$title'" );
    $form = '';
    foreach ( $results as $result ) {
        $title = $result->title;
        $locality = $result->locality;
        $address = $result->address;
        $phone = $result->phone;
        $cord1 = $result->cord1;
        $cord2 = $result->cord2;
        $form .= '<div class="csv-data">  <h3>'.$title.'</h3>  <iframe class="gmap_iframe" frameborder="0" scrolling="no"
        marginheight="0" marginwidth="0"
        src="https://maps.google.com/maps?q='.$cord1.','.$cord2.'&hl=es;z=14&amp;output=embed"> </iframe><p>Locality: ' . $locality . '</p><p>Address: ' . $address . '</p><p>Phone: ' . $phone . '</p> <hr></hr></div>';
    }
    return $form;
}
add_shortcode( 'replace', 'replace_shortcode' );

function csv_get_data_replace_content( $content ) {
    if ( strpos( $content, '[csvnull]' ) !== false ) {
        $form = do_shortcode( '[replace]' );
        $content = str_replace( '[csvnull]', $form, $content );
    }
    return $content;
}
add_filter( 'the_content', 'csv_get_data_replace_content' );


?>

<?php
