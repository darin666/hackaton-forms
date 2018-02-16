<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function print_form_input($name, $label, $data = [])
{
    echo "<label>{$label}:
            <input type=\"text\" name=\"{$name}\" value=\"";
    if(isset($data[$name]))  echo htmlspecialchars($data[$name]);
    echo "\">
        </label><br />
    ";
}

$pdo_connection = new PDO(
    'mysql:dbname=guest_list;host=localhost;charset=utf8',
    'root',
    'rootroot'
);

$pdo_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//var_dump($pdo_connection);

$success = filter_input(INPUT_GET, 'success');

//var_dump($_POST);

if (count($_POST) > 0)
{
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $seat_number = filter_input(INPUT_POST, 'seat_number', FILTER_VALIDATE_INT);
    $row_number = filter_input(INPUT_POST, 'row_number', FILTER_VALIDATE_INT);
    $notice = filter_input(INPUT_POST, 'notice');
    $action = filter_input(INPUT_POST, 'action');

    $not_allow_to_do = $name == '' || !$email || FALSE === $seat_number || FALSE === $row_number || $notice == '';

    if ($not_allow_to_do)
    {
        header('Location: ?success=no');
    } elseif ($action == 'edit') {
        $my_statement = $pdo_connection->prepare('UPDATE guest_list SET
        `name` = ?, email = ?, seat_number = ?, row_number = ?, notice = ?
        WHERE id = ? LIMIT 1');
        $result = $my_statement->execute([$name, $email, $seat_number, $row_number, $notice, $id]);

        header('Location: ?success=yes');
    } else {
        $my_statement = $pdo_connection->prepare('INSERT INTO guest_list
        (`name`, email, seat_number, row_number, notice, created_at) VALUES
        (     ?,     ?,           ?,          ?,      ?,      now())');
        $result = $my_statement->execute([$name, $email, $seat_number, $row_number, $notice]);
        header('Location: ?success=yes');
    }
}

if (isset($_GET["id"]) && $_GET["action"] == 'delete') {
    // sql to delete a record
    $my_statement = $pdo_connection->prepare('DELETE FROM `guest_list` WHERE ID = ? LIMIT 1');
    $my_statement->execute([$_GET['id']]);
    header("location:test.php");
    exit;
}

// here ends the action part and begins the output

$edit_row = [];
if (isset($_GET["id"]) && $_GET["action"] == 'edit') {
    $my_statement = $pdo_connection->prepare('SELECT * FROM `guest_list` WHERE ID = ? LIMIT 1');
    $my_statement->execute([$_GET['id']]);
    $edit_row = $my_statement->fetch(PDO::FETCH_ASSOC);
var_dump($edit_row);
}

?>

<html>
<head>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
    <body>

        <?php
        // echo "<pre>";
        // print_r($_GET);
        // echo "</pre>";

        //echo $_GET["id"];
        ?>

        <?php if ($success == 'no') echo '<p class="label label-danger">FAIL</p>'; ?>
        <?php if ($success == 'yes') echo '<p class="label label-success">SUCCESS</p>'; ?>


        <form method="post">
<?php if (filter_input(INPUT_GET, 'action') == 'edit') {
    echo 'id<input type="hidden" name="id" value="'.$edit_row['id'].'" /><br>';
    echo 'action<input type="hidden" name="action" value="edit" /><br>';

} ?>
        <?php echo print_form_input('name', 'Name', $edit_row); ?>
        <?php echo print_form_input('email', 'Email', $edit_row); ?>
        <?php echo print_form_input('seat_number', 'Seat number', $edit_row); ?>
        <?php echo print_form_input('row_number', 'Row number', $edit_row); ?>

            <label>Notice:
                <textarea name="notice" cols="30" rows="5"><?php
                if (isset($edit_row['notice'])) {
                    echo htmlspecialchars($edit_row['notice']);
                }
                ?></textarea>
                <br/>
            </label>
                <br/>

            <input type="submit" value="Submit" class="btn btn-lg btn-success">

        </form>

        <table border="1">
        <?php
        $my_statement = $pdo_connection->query('SELECT * FROM `guest_list`');

        foreach ($my_statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            echo "<tr><td>" . join('</td><td>', $row) .'</td>
            <td><a href="?id='.$row["id"].'&amp;action=edit">Edit</a></td>
            <td><a href="?id='.$row["id"].'&amp;action=delete" onclick="return confirm(\'Are you sure?\');">Delete</td>
            </tr>';
        }

        ?>
        </table>

    </body>

</html>