<?php include 'task_file_parser.php';
$link = mysqli_connect('db', 'nigel', 'passw0rd', 'sample_d');  // (host (name in docker), username, password, database name)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Assembly Task Project</title>
    <!-- css link goes here -->
</head>
<body>
    <? 
    print "<pre>"; print_r($_POST); print "</pre>";
    print "<pre>"; print_r($_GET); print "</pre>";

    if ($_POST['Function']=="SaveTask"){
        print "I am trying to save task ".$_POST['Title'];
    }

    ?>
    <?php if ($_GET['id']) {

        $id_check_query =  "SELECT * FROM Tasks WHERE id = ?";
        $id_check_statement = mysqli_prepare($link, $id_check_query);
        mysqli_stmt_bind_param($id_check_statement, "i", $_GET['id']);
        mysqli_stmt_execute($id_check_statement);
        $result = mysqli_stmt_get_result($id_check_statement);
        if (mysqli_num_rows($result) == 0) {
            echo "<h2>❌ No result found for that id. </h2>";
        }
        else {
            $task = mysqli_fetch_assoc($result);
            $id = $task['id'];
            $title = $task['Title'];
            $secondsToComplete = $task['SecondsToComplete'];
            $description = $task['Description'];
            $groupName = $task['GroupName'];
            $fileName = $task['FileName'];
            $dateCreated = $task['DateCreated'];
            $dateUpdated = $task['DateUpdated'];
            $taskNum = $task['TaskNum'];

            ?>  
                <h2>📝Edit Task</h2>
                <form method="post">
                    <input type="hidden" name="id" value=<?php echo $id; ?>/>

                    <div class="task-row">
                        <label for="title">Title: </label>
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo $title; ?>" required>
                    </div>

                    <div class="task-row">
                        <label for="secondsToComplete">Seconds to Complete: </label>
                        <input type="number" id="secondsToComplete" name="secondsToComplete" value="<?php echo $secondsToComplete ?>" required>
                    </div>

                    <div class="task-row">
                        <label for="description">Description: </label>
                        <textarea id="description" name="description" rows="4" cols="50"><?php echo $description ?></textarea>
                    </div>

                    <div class="task-submit-btn">
                        <button type="submit" name="SubmitTaskChanges" value="SaveTask">Save</button>
                        <a href="index.php">Cancel</a>
                    </div>

                </form>

    
            <?php

        }
        //     $insert_query = "INSERT INTO Tasks (Title, SecondsToComplete, Description, GroupName, FileName, TaskNum) VALUES (?, ?, ?, ?, ?, ?)";
        //     $insert_statement = mysqli_prepare($link, $insert_query);
        //     mysqli_stmt_bind_param($insert_statement, 'sisssi', $task['Title'], $task['SecondsToComplete'], $task['Description'], $task['GroupName'], $task['FileName'], $task['TaskNum']);
        //     mysqli_stmt_execute($insert_statement);
        //     mysqli_stmt_close($insert_statement);
        //     echo "Added task: " . $task['Title'] . "<br>";
        
        // mysqli_stmt_close($check_statement);
        

        return ; 
    } ?>
    <h1>Tasks</h1>
    <?php 
        $task_folder = './all_tasks/' ; 
        $task_files = scandir($task_folder) ; 

        if ($link) {
            echo "<h3>✅ Successfully connected to database!</h2>";
            $result = mysqli_query($link, "SELECT * FROM Tasks");
            echo "<h4>Currently in Database: </h4>";
            while ($row = mysqli_fetch_assoc($result)) { ?>
                <div class="task">
                    <?php echo $row['Title']; ?>
                </div>
            <?php
            }
        } 
        else {
            echo "<h3>❌Database connection error </h2>";
        }

        // for parsed data
        $all_data = [];

        foreach ($task_files as $task_file) {
            if (is_dir($task_folder . $task_file) || $task_file[0] === '.') {
                continue;
            }

            $file_contents = file_get_contents($task_folder . $task_file);

            $task_data = parseTaskFile($file_contents);

            if ($task_data === null) {
                continue;
            }

            $all_data[] = $task_data;

        }

        foreach ($all_data as $task) {
            $check_query =  "SELECT * FROM Tasks WHERE FileName = ?";
            $check_statement = mysqli_prepare($link, $check_query);
            mysqli_stmt_bind_param($check_statement, "s", $task['FileName']);
            mysqli_stmt_execute($check_statement);
            $result = mysqli_stmt_get_result($check_statement);
            if (mysqli_num_rows($result) == 0) {
                $insert_query = "INSERT INTO Tasks (Title, SecondsToComplete, Description, GroupName, FileName, TaskNum) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_statement = mysqli_prepare($link, $insert_query);
                mysqli_stmt_bind_param($insert_statement, 'sisssi', $task['Title'], $task['SecondsToComplete'], $task['Description'], $task['GroupName'], $task['FileName'], $task['TaskNum']);
                mysqli_stmt_execute($insert_statement);
                mysqli_stmt_close($insert_statement);
                echo "Added task: " . $task['Title'] . "<br>";
            }
            mysqli_stmt_close($check_statement);
        }
        ?>

        <h2>Table View</h2>
        <table id="dataTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Group Name</th>
                    <th>File Name</th>
                    <th>Title</th>
                    <th>Date Created</th>
                    <th>Date Updated</th>
                    <th>Task #</th>
                    <th>Time (sec)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_data as $task_data): ?>
                <tr>
                    <td><?php echo $task_data['id']; ?></td>
                    <td><?php echo $task_data['GroupName']; ?></td>
                    <td><?php echo $task_data['FileName']; ?></td>
                    <td><?php echo $task_data['Title']; ?></td>
                    <td><?php echo $task_data['DateCreated']; ?></td>
                    <td><?php echo $task_data['DateUpdated']; ?></td>
                    <td><?php echo $task_data['TaskNum']; ?></td>
                    <td><?php echo $task_data['SecondsToComplete']; ?></td>
                    <td><a href="?id=<?=$task_data['id']?>">edit</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

</body>
</html>