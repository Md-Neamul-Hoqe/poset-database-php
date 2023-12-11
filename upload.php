<?php
// include "db_conn.php";
?>
<!-- Bootstrap 5.2  -->
<?php include "header.php"; ?>
    <title>Upload Posets To The Database</title>
</head>

<body>
    <header class="header-section mt-0 mb-5">
        <?php include "menu.php";
        if (!isset($_SESSION['id']) && !isset($_SESSION['user_name'])) {
            header("location: ./index.php"); /* If not signed in */
        } else {
        ?>
    </header>
    <main class="content-wrapper py-5">
        <section id="connposets-upload-section" class="px-3">
            <!-- ################## MUST BE METHOD == POST FOR HANDLING FILES ##########################-->
            <div class="row">
                <div class="col-12 col-md-9">
                    <form class="form-control w-100 clearfix" action="<?php $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data" id="inputForm">
                        <table class="updateTable w-100">
                            <thead>
                                <tr>
                                    <th class="text-center p-2 fs-4">Add New Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <!-- For catch multiple files by PHP using POST must be input name with array sign "[]" ?  -->
                                    <td><label for="mfiles">Enter The Poset-Matrices As DAT File</label><input data-bs-toggle='tooltip' title="Inser The Files Here" type="file" name="mfiles[]" id="Mfiles" placeholder="Upload The Data Files With Specific Name." multiple></td>
                                </tr>
                            </tbody>
                        </table>
                        <input class="text-info bg-transparent" type="submit" value="Upload" name="upload">
                        <!-- <input class="col-2 float-end bg-transparent text-danger" type="submit" value="Update" name="update"> -->
                    </form>
                </div>
                <div class="col-12 col-md-3">
                    <form class="form-control w-100 clearfix" action="<?php $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data" id="inputForm">
                        <table class="updateTable w-100">
                            <thead>
                                <tr>
                                    <th class="text-center p-2 fs-4">Update To Allposets</th>
                                </tr>
                            </thead>
                        </table>
                        <!-- <input class="col-2 float-start text-info bg-transparent" type="submit" value="Upload" name="upload"> -->
                        <input class="bg-transparent text-danger" type="submit" value="Update" name="update">
                    </form>
                </div>
            </div>
            <!-- ######################################################################## -->

            <!-- ################## Show OUTPUT ################## -->
            <?php
            include "db_conn.php";

            /** 
             * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
             * Update Types To Allposets From connposets & disconnposets, if not updated 
             * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
             * */
            if (isset($_POST["update"])) {
                echo "Updating Type of Allposets Table.<br/>";

                $query = "SELECT allposets.`idx`, allposets.`Type`
                          FROM allposets
                          INNER JOIN (SELECT `Matrix`, 'con' AS `Type` FROM connposets
                                      UNION ALL
                                      SELECT `Matrix`, 'dis' AS `Type` FROM disconnposets) AS temp
                          ON allposets.`Matrix` = temp.`Matrix`
                          WHERE allposets.`Type` <> temp.`Type`";

                $result = mysqli_query($conn, $query);
                // echo '<pre>';
                // print_r($result);
                // echo '</pre>';

                if (!$result || mysqli_num_rows($result) < 1) {
                    echo "No rows to update.";
                    exit();
                }

                while ($row = mysqli_fetch_assoc($result)) {
                    $type = $row['Type'];
                    $idx = $row['idx'];
                    echo "<pre>";
                    print_r($row);
                    echo "</pre>";
                    $updateQuery = "UPDATE `allposets` SET `Type` = ? WHERE `allposets`.`idx` = ?";
                    $updateStmt = mysqli_prepare($conn, $updateQuery);
                    mysqli_stmt_bind_param($updateStmt, 'si', $type, $idx);
                    mysqli_stmt_execute($updateStmt);
                }

                echo "Update successful.<br/>";
            }


            /**
             * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
             *  Upload The New Files 
             * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
             * */
            if (isset($_POST['upload'])) {
                // echo "<pre>";
                // print_r($_FILES['mfiles']);
                // echo "</pre>";

                if (!empty($_FILES['mfiles']) && !empty($_FILES['mfiles']['name'])) {
                    foreach ($_FILES['mfiles']['name'] as $key => $filename) {      // FOR EACH FILES GET FROM INPUT FILED RUN THIS LOOP
                        // echo "<br>" . $key . "<br>";
                        // print_r($_FILES['mfiles']['name'][$key]);


                        /**
                         *  Image FILE UPLOAD 
                         * */
                        /* Save Images To */


                        /* Valid Image Types */
                        $allowed_types = array('image/jpeg', 'image/png');
                        // foreach ($_FILES['mfiles']['name'] as $key => $filename) {

                        /* Get The Type Of The File */
                        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                        // echo $fileInfo;

                        $Folder = "Database/MatrixFiles/";
                        if (!finfo_file($fileInfo, $Folder . $filename)) {
                            echo "Get The File Info.<br/>";
                        }

                        /* Is The Type Exist in Valid Types */
                        if (in_array(finfo_file($fileInfo, $Folder . $filename), $allowed_types)) {
                            $Folder = "Database/hasseDiagrams/";
                            echo "<br>An image \"" . $filename . "\" is detected of valid type: " . finfo_file($fileInfo, $Folder . $filename);

                            /* Move the file to desired Folder */
                            $tmpName = $_FILES['mfiles']['tmp_name'][$key]; // Uploaded files saved here by default

                            // echo $Folder;
                            move_uploaded_file($tmpName, $Folder . $filename);

                            echo "<br/>Uploaded Successfully.";
                        } else {

                            /**
                             *  Dat Files Upload 
                             * */

                            echo "Not a Image file. It is a ", finfo_file($fileInfo, $Folder . $filename), " type.<br>";

                            $tmpName = $_FILES['mfiles']['tmp_name'][$key]; // Uploaded files saved here by default

                            move_uploaded_file($tmpName, $Folder . $filename);
                            echo "Uploaded to ";
                            echo $Folder;

                            /* READ THE FILE AND STORE THE DATA IN $FILES */
                            $Files = file($Folder . $filename) or die("<span class='error'>No " . $filename . " named file exists in such directory.</span>");

                            /* EXTRACT DATABASE TABLE-NAME & ORDER OF THE MATRIX FROM THE FILE-NAME-STRING  */
                            $tableName = preg_split("/[0-9 . _]+/", "$filename");   // Remove numbers from $filename
                            $Numbers = preg_split("/[ a-z A-Z . _]+/", "$filename");     // filename Formate => allposets2.dat, connposets2_3.dat, disconnposets2_3.dat
                            echo "<pre>";
                            print_r($Numbers);
                            echo "</pre>";
                            // Order of the Poset [tableNameMOrder_Height_Width.dat]
                            $MOrder = (int)$Numbers[1];

                            if (substr($tableName[0], 0, 3) === 'con') {

                                if (isset($Numbers[3]) && $Numbers[3]) { /* Skip if Width is not found */
                                    echo "Connected Posets";
                                } else {
                                    echo "skipped the file.";
                                    continue;
                                }


                                //################## 
                                //##################  FOR CONNPOSETS.DAT
                                //################## 
                                $Height = (!empty($Numbers[2]) && $Numbers[2] > 0) ? (int)$Numbers[2] : 0;
                                $Width = (!empty($Numbers[3]) && $Numbers[3] > 0) ? (int)$Numbers[3] : 0;


                                // CHECK THE TABLE EXISTS
                                $query = "SELECT $tableName[0].`idx` FROM $tableName[0]";
                                $result = mysqli_query($conn, $query);  // Can Select or Not

                                $Status = "the";        // THE TABLE IS NEW OR OLD 

                                /* If Such Table not exit in database */
                                if (!$result) {
                                    // CREATE THE TABLE IN THE SELECTED DATABASE IF NOT EXISTS i.e. QUERY FAILED: SELECT

                                    /* For table "connposets" */
                                    $createTable = mysqli_query($conn, "CREATE TABLE `$db_name`.`$tableName[0]`(
                                        `idx` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                        PRIMARY KEY(`idx`),
                                        `MatrixOrder` INT(2) UNSIGNED NOT NULL,
                                        INDEX(`MatrixOrder`),
                                        `Matrix` VARCHAR(380) NOT NULL,
                                        UNIQUE(`Matrix`), 
                                        `Height` INT(4) UNSIGNED NOT NULL,
                                        INDEX(`Height`),
                                        `Width` INT(4) UNSIGNED NOT NULL,
                                        INDEX(`Width`),
                                        `Date` DATETIME NOT NULL
                                )") or die("<br/><span class='error'>" . mysqli_errno($conn) . ": " . mysqli_error($conn) . "</span><br/>");
                                    $Status = "A New";
                                    echo "<div class='error text-info text-center mx-auto'>A '$tableName[0]' Named Table Created.</div>";
                                } //=== END if (!$result)       //  SELECTION QUERY END

                                //========== UPLOAD THE EXTRACTED MATRICES TO THE DATABASE ==============//
                                $numUploadedM = 0;          // how many New Poset-matrix found.
                                if ($numUploadedM == 0) {
            ?>
                                    <table id="Height-searchedTable" class="mx-auto border-top border-3 border-danger mt-5 table-border">
                                        <thead>
                                            <tr>
                                                <td colspan="4" class="text-center fw-bold fs-2 vw-100">Uploading Results <?php if (!empty($_GET['morder'])) {
                                                                                                                                echo " For Order = $MOrder";
                                                                                                                            }
                                                                                                                            ?></td>
                                            </tr>
                                            <tr>
                                                <th class="pb-1 fs-5 text-center text-light bg-gradient bg-dark border border-secondary">No.</th>
                                                <th class="pb-1 fs-5 text-center text-light bg-gradient bg-dark border border-secondary">Matrix Order</th>
                                                <th class="pb-1 fs-5 text-center text-light bg-gradient bg-dark border border-secondary">Height</th>
                                                <th class="pb-1 fs-5 text-center text-light bg-gradient bg-dark border border-secondary">Width</th>
                                                <th class="pb-1 fs-5 text-center text-light bg-gradient bg-dark border border-secondary">Matrices</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                        }
                                        echo "<br/>The Order of The D/C-poset Matrix is " . $MOrder;
                                        if ($Height > 0 && $Width > 0) {
                                            echo " With Height ", $Height, " & Width ", $Width;
                                        }

                                        ///////////////// GET THE MATRIX FROM UPLAODED FILEs \\\\\\\\\\\\\\\
                                        $linesInFile = count($Files);
                                        $nthMatrix = preg_split("/[,]+/", "$Files[0]");
                                        // $nthMatrix = 
                                        // echo '<pre>';
                                        // print_r(json_encode(explode('\n', $Files[0])));
                                        // echo '</pre>';

                                        // $nthMatrix = $Files[0];

                                        $numElementofaM = sizeof($nthMatrix);
                                        echo "<br> The no. of Elements in a line is " . $numElementofaM . " & no. of lines is " . $linesInFile;
                                        $lines = 0;
                                        while ($lines < $linesInFile) {     // RUN THE LOOP UPTO EOF'S LINES || fgets() not work with array.
                                            $ExtractMFromFile = trim($Files[$lines]);
                                            $k = 0;
                                            while ($k < 2 * $numElementofaM - 1) {
                                                $ExtractMFromFile[$k];
                                                if ($ExtractMFromFile[$k] === ',') {
                                                    $ExtractMFromFile[$k] = ' ';
                                                } else {
                                                    $ExtractMFromFile[$k] = (int) $ExtractMFromFile[$k];
                                                    //  Save the elements of the upper triangular poset-matrix
                                                }
                                                $k++;
                                            }
                                            $Matrix = $ExtractMFromFile;
                                            echo '<pre>';
                                            print_r($Matrix);
                                            echo '</pre>';

                                            $today = date("Y-m-j H:i:s");     // Without any text values => 2022-10-05 10:57:10 For DateTime var type

                                            ///////////////// UPLOAD THE MATRICES TO THE DATABASE TABLE \\\\\\\\\\\\\\\
                                            //------------// ERROR NOTE: Quot the TABLE & VALUES, if not, insertion failed without showing any error in browser //--------------//
                                            $queryFind  = "SELECT $tableName[0].`idx` FROM $tableName[0] WHERE $tableName[0].`Matrix` IN ('$Matrix')";
                                            $Find_1 = mysqli_query($conn, $queryFind) or die("<div class='error' style='margin-top: 10rem'>May $tableName[0] named table in database isn't exist.</div>");

                                            /* To use the associative index later */
                                            $rows = mysqli_fetch_assoc($Find_1);

                                            $updated = false;

                                            /* CONNPOSETS.DAT */
                                            if (!$rows) {
                                                $queryInsertion = "INSERT INTO `$tableName[0]` (`idx`, `MatrixOrder`, `Matrix`, `Height`, `Width`, `Date`) VALUES (NULL, '$MOrder', '$Matrix', '$Height', '$Width', '$today')";
                                                $insert_2 = mysqli_query($conn, $queryInsertion) or die("Error: " . mysqli_errno($conn) . ": " . mysqli_error($conn));
                                            ?>
                                                <tr>
                                                    <td class="border fs-5 text-center"> <?php echo $lines ?></td>
                                                    <td class="border fs-5 text-center"> <?php echo $MOrder ?></td>
                                                    <td class="border fs-5 text-center"> <?php echo $Height ?></td>
                                                    <td class="border fs-5 text-center"> <?php echo $Width ?></td>
                                                    <td class="border fs-5 text-center"> <?php echo $Matrix ?></td>
                                                </tr>
                                            <?php
                                                $numUploadedM = $numUploadedM + 1;      // How many new matrix inserted in the table 
                                            } else {        // What's The ERROR: No Old Matrix exist in table nor new matrix found :) 

                                                $updateQuery = "UPDATE `$tableName[0]` SET `Height` = '$Height', `Width` = '$Width' WHERE `$tableName[0]`.`idx` = $rows[idx] AND `$tableName[0]`.`Matrix` = '$Matrix' AND $tableName[0].`Height` <> $Height AND $tableName[0].`Width` <> $Width";
                                                $updateRun = mysqli_query($conn, $updateQuery);
                                                if ($updateRun) $updated = true;
                                            ?>
                                                <tr>
                                                    <td class="border fs-5 text-center"> <?php echo $lines ?></td>
                                                    <td class="border fs-5 text-center"> <?php echo $MOrder ?></td>
                                                    <td class="border fs-5 text-center"> <?php echo $Height ?></td>
                                                    <td class="border fs-5 text-center"> <?php echo $Width ?></td>
                                                    <td class="border fs-5 text-center"> <?php echo $Matrix ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="border fs-5 text-center" colspan="5"> <?php echo (!$updated) ? "<span class='error border border-dark text-center mx-auto'>" . mysqli_errno($conn) . ": The Matrix \" $Matrix \" already exists in \" $tableName[0] \".</span><br/>" : "Updated Height as ", $Height, " & Width ", $Width; ?> </td>
                                                </tr>
                                            <?php
                                            }   //=== END IF (MYSQLI_QUERY($CONN, $QUERYINSERTION)) 
                                            $today = date("Y-m-j H:i:s");     // 2022-10-05 10:57:10
                                            $lines++;
                                        }   //===  END WHILE ($LINES < $LINESINFILE) 
                                        echo '<tr class="min-vw-100"><td class="border fs-5 text-center border border-danger border-3" colspan="5">';
                                        echo "<br/><span class='text-info'>Note:</span> <span class='text-danger'>$numUploadedM </span>New Matrices of Order $MOrder Uploaded From '$filename' named file to $Status table '$tableName[0]' in database '$db_name'.";
                                        echo "</td></tr>";
                                    } else if (substr($tableName[0], 0, 3) === 'dis') {

                                        if (isset($Numbers[2]) && $Numbers[2]) { /* Skip if Direct Term is not found */
                                            echo "Dis Connected Posets";
                                        } else {
                                            echo "skipped the file.";
                                            continue;
                                        }


                                        //################## 
                                        //##################  FOR DISCONN-POSETS.DAT
                                        //################## 
                                        $Num_Height = (!empty($Numbers[2]) && $Numbers[2] > 0) ? (int)$Numbers[2] : 0;
                                        // $Width = (!empty($Numbers[3]) && $Numbers[3] > 0) ? (int)$Numbers[3] : 0;


                                        // CHECK THE TABLE EXISTS
                                        $query = "SELECT $tableName[0].`idx` FROM $tableName[0]";
                                        $result = mysqli_query($conn, $query);  // Can Select or Not

                                        $Status = "the";        // THE TABLE IS NEW OR OLD 

                                        /* If Such Table not exit in database */
                                        if (!$result) {
                                            // CREATE THE TABLE IN THE SELECTED DATABASE IF NOT EXISTS i.e. QUERY FAILED: SELECT

                                            /* For table "disconnposets" */
                                            $createTable = mysqli_query($conn, "CREATE TABLE `$db_name`.`$tableName[0]`(
                                            `idx` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                            PRIMARY KEY(`idx`),
                                            `MatrixOrder` INT(2) UNSIGNED NOT NULL,
                                            INDEX(`MatrixOrder`),
                                            `Matrix` VARCHAR(380) NOT NULL,
                                            UNIQUE(`Matrix`), 
                                            `Height` INT(4) UNSIGNED NOT NULL,
                                            INDEX(`Height`),
                                            `Date` DATETIME NOT NULL
                                )") or die("<br/><span class='error'>" . mysqli_errno($conn) . ": " . mysqli_error($conn) . "</span><br/>");
                                            $Status = "A New";
                                            echo "<div class='error text-info text-center mx-auto'>A '$tableName[0]' Named Table Created.</div>";
                                        } //=== END if (!$result)       //  SELECTION QUERY END

                                        //========== UPLOAD THE EXTRACTED MATRICES TO THE DATABASE ==============//
                                        $numUploadedM = 0;          // how many New Poset-matrix found.
                                        if ($numUploadedM == 0) {
                                            ?>
                                            <table id="Height-searchedTable" class="mx-auto border-top border-3 border-danger mt-5 table-border">
                                                <thead>
                                                    <tr>
                                                        <td colspan="4" class="text-center fw-bold fs-2 vw-100">Uploading Results <?php if (!empty($_GET['morder'])) {
                                                                                                                                        echo " For Order = $MOrder";
                                                                                                                                    }
                                                                                                                                    ?></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="pb-1 fs-5 text-center text-light bg-gradient bg-dark border border-secondary">No.</th>
                                                        <th class="pb-1 fs-5 text-center text-light bg-gradient bg-dark border border-secondary">Matrix Order</th>
                                                        <th class="pb-1 fs-5 text-center text-light bg-gradient bg-dark border border-secondary">Direct Terms</th>
                                                        <th class="pb-1 fs-5 text-center text-light bg-gradient bg-dark border border-secondary">Matrices</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                }
                                                echo "<br/>The Order of The Disconnected poset Matrix is $MOrder With Height $Num_Height";

                                                ///////////////// GET THE MATRIX FROM UPLAODED FILEs \\\\\\\\\\\\\\\
                                                $linesInFile = count($Files);
                                                $nthMatrix = preg_split("/[,]+/", "$Files[0]");
                                                $numElementofaM = sizeof($nthMatrix);
                                                echo "<br> The no. of Elements in a line is $numElementofaM  & no. of lines is $linesInFile";
                                                $lines = 0;
                                                while ($lines < $linesInFile) {     // RUN THE LOOP UPTO EOF'S LINES || fgets() not work with array.
                                                    $ExtractMFromFile = trim($Files[$lines]);
                                                    $k = 0;
                                                    while ($k < 2 * $numElementofaM - 1) {
                                                        $ExtractMFromFile[$k];
                                                        if ($ExtractMFromFile[$k] === ',') {
                                                            $ExtractMFromFile[$k] = ' ';
                                                        } else {
                                                            $ExtractMFromFile[$k] = (int) $ExtractMFromFile[$k];
                                                            //  Save the elements of the upper triangular poset-matrix
                                                        }
                                                        $k++;
                                                    }
                                                    $Matrix = $ExtractMFromFile;

                                                    $today = date("Y-m-j H:i:s");     // Without any text values => 2022-10-05 10:57:10 For DateTime var type

                                                    ///////////////// UPLOAD THE MATRICES TO THE DATABASE TABLE \\\\\\\\\\\\\\\
                                                    //------------// ERROR NOTE: Quot the TABLE & VALUES, if not, insertion failed without showing any error in browser //--------------//
                                                    $queryFind  = "SELECT $tableName[0].`idx` FROM $tableName[0] WHERE $tableName[0].`Matrix` IN ('$Matrix')";
                                                    $Find_1 = mysqli_query($conn, $queryFind) or die("<div class='error' style='margin-top: 10rem'>May $tableName[0] named table in database isn't exist.</div>");

                                                    /* To use the associative index later */
                                                    $rows = mysqli_fetch_assoc($Find_1);

                                                    $updated = false;

                                                    /* CONNPOSETS.DAT & DISCONN-POSETS.DAT */
                                                    if (!$rows) {
                                                        $queryInsertion = "INSERT INTO `$tableName[0]` (`idx`, `MatrixOrder`, `Matrix`, `Height`, `Date`) VALUES (NULL, '$MOrder', '$Matrix', '$Num_Height', '$today')";
                                                        $insert_2 = mysqli_query($conn, $queryInsertion) or die("Error: " . mysqli_errno($conn) . ": " . mysqli_error($conn));
                                                    ?>
                                                        <tr>
                                                            <td class="border fs-5 text-center"> <?php echo $lines ?></td>
                                                            <td class="border fs-5 text-center"> <?php echo $MOrder ?></td>
                                                            <td class="border fs-5 text-center"> <?php echo $Num_Height ?></td>
                                                            <td class="border fs-5 text-center"> <?php echo $Matrix ?></td>
                                                        </tr>
                                                    <?php
                                                        $numUploadedM = $numUploadedM + 1;      // How many new matrix inserted in the table 
                                                    } else {        // What's The ERROR: No Old Matrix exist in table nor new matrix found :) 

                                                        $updateQuery = "UPDATE `$tableName[0]` SET `Height` = '$Num_Height' WHERE `$tableName[0]`.`idx` = $rows[idx] AND `$tableName[0]`.`Matrix` = '$Matrix' AND $tableName[0].`Height` <> $Num_Height";
                                                        $updateRun = mysqli_query($conn, $updateQuery);
                                                        $updated = true;
                                                    ?>
                                                        <tr>
                                                            <td class="border fs-5 text-center"> <?php echo $lines ?></td>
                                                            <td class="border fs-5 text-center"> <?php echo $MOrder ?></td>
                                                            <td class="border fs-5 text-center"> <?php echo $Num_Height ?></td>
                                                            <td class="border fs-5 text-center"> <?php echo $Matrix ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border fs-5 text-center" colspan="5"> <?php echo (!$updated) ? "<span class='error border border-dark text-center mx-auto'>" . mysqli_errno($conn) . ": The Matrix \" $Matrix \" already exists in \" $tableName[0] \".</span><br/>" : "Updated Direct Terms as ", $Num_Height; ?> </td>
                                                        </tr>
                                                    <?php
                                                    }   //=== END IF (MYSQLI_QUERY($CONN, $QUERYINSERTION)) 
                                                    $today = date("Y-m-j H:i:s");     // 2022-10-05 10:57:10
                                                    $lines++;
                                                }   //===  END WHILE ($LINES < $LINESINFILE) 
                                                echo '<tr class="min-vw-100"><td class="border fs-5 text-center border border-danger border-3" colspan="5">';
                                                echo "<br/><span class='text-info'>Note:</span> <span class='text-danger'>$numUploadedM </span>New Matrices of Order $MOrder Uploaded From '$filename' named file to $Status table '$tableName[0]' in database '$db_name'.";
                                                echo "</td></tr>";
                                            } else {
                                                //##################  FOR ALLPOSETS.DAT
                                                ///////////////// GET THE MATRIX FROM UPLAODED FILEs \\\\\\\\\\\\\\\
                                                //################## 

                                                // CHECK THE TABLE ASSOCIATIVE NAMED TO THIS FILE IS EXISTS OR NOT.
                                                $queryFindDB = "SELECT $tableName[0].`idx` FROM $tableName[0]";
                                                $resultFindDB = mysqli_query($conn, $queryFindDB);
                                                $Status = "the";        // THE TABLE / A NEW Table || IS TABLE NEW OR OLD 

                                                // CREATE THE TABLE IN THE SELECTED DATABASE IF NOT EXISTS i.e. QUERY FAILED: SELECT
                                                if (!$resultFindDB) {
                                                    $createTable = mysqli_query($conn, "CREATE TABLE `$db_name`.`$tableName[0]`(
                                                    `idx` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                                                    PRIMARY KEY(`idx`),

                                                    `MatrixOrder` INT(4) UNSIGNED NOT NULL,
                                                    INDEX(`MatrixOrder`),

                                                    `Matrix` VARCHAR(380) NOT NULL,
                                                    UNIQUE(`Matrix`), 

                                                    `IsomorphicToMatrix` VARCHAR(65535) NOT NULL,
                                                    -- UNIQUE(`IsomorphicToMatrix`), -- uncomment when this type of data uploaded otherwise it's show error for not unique value

                                                    `Type` CHAR(3) NOT NULL,
                                                    INDEX(`Type`),                                                          
                                                    `Date` DATETIME NOT NULL
                                                    )") or die("<br/><span class='error'>" . mysqli_errno($conn) . ": " . mysqli_error($conn) . "</span><br/>");
                                                    $Status = "A New";
                                                    echo "<div class='error text-info text-center mx-auto'>A '$tableName[0]' Named Table Created.</div>";
                                                } //=== END if (!$result)       //  Table Created

                                                //################# If Table Already Exists then UPLOAD THE EXTRACTED MATRICES TO THE DATABASE
                                                $numUploadedM = 0;          // how many Poset-matrix counted.
                                                if ($numUploadedM === 0) {
                                                    ?>
                                                    <table id="Height-searchedTable" class="mx-auto border-top border-3 border-danger mt-5 table-border">
                                                        <!-- Header of the Output table -->
                                                        <thead>
                                                            <tr>
                                                                <td colspan="4" class="text-center fw-bold fs-2 vw-100">Uploading Results <?php if (!empty($_GET['morder'])) {
                                                                                                                                                echo " For Order = $MOrder";
                                                                                                                                            }
                                                                                                                                            ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th class="pb-1 fs-5 text-center text-light bg-gradient bg-dark border border-secondary">Matrices</th>
                                                            </tr>
                                                        </thead>
                                                        <!-- Body of the output table -->
                                                        <tbody>
                                                            <?php
                                                        }
                                                        // number of elements of the upper triangular poset-matrix / Number of elements in a line of the file.
                                                        echo "<br/>The Order of The Matrix is " . $MOrder;
                                                        echo "<br> The Number of Lines in this file is " . $linesInFile = count($Files);
                                                        $nthMatrix = preg_split("/[,]+/", "$Files[0]");
                                                        echo "<br> The Number of Elements in a line is " . $numElementofaM = sizeof($nthMatrix);

                                                        /* Insert Every Poset-Matrix As A New Row To The Database Table */
                                                        $lines = 0;
                                                        //------------// ERROR NOTE: Not working in foreach() //--------------//
                                                        while ($lines < $linesInFile) {     // RUN THE LOOP UPTO EOF'S LINES
                                                            $ExtractMFromFile = trim($Files[$lines]);
                                                            $k = 0;
                                                            while ($k < 2 * $numElementofaM - 1) {
                                                                $ExtractMFromFile[$k];
                                                                if ($ExtractMFromFile[$k] === ',') {
                                                                    $ExtractMFromFile[$k] = ' ';
                                                                } else {
                                                                    $ExtractMFromFile[$k] = (int) $ExtractMFromFile[$k];
                                                                    //  Save the elements of the upper triangular poset-matrix /* Array To Matrix */
                                                                }
                                                                $k++;
                                                            }
                                                            $Matrix = $ExtractMFromFile;
                                                            $today = date("Y-m-j H:i:s");     // Without any text values like "2022-10-05 10:57:10" For DateTime var type

                                                            ///////////////// UPLOAD THE MATRICES TO THE DATABASE TABLE \\\\\\\\\\\\\\\
                                                            // Check The Matrix Already Exists or Not.
                                                            $queryFind  = "SELECT $tableName[0].`idx` FROM $tableName[0] WHERE $tableName[0].`Matrix` IN ('$Matrix')";
                                                            $Find_1 = mysqli_query($conn, $queryFind);

                                                            $updated = false;
                                                            $insert_2 = 0;
                                                            // If New Matrix Found Then Insert
                                                            if (!($Find_1->num_rows)) {
                                                                $queryInsertion  = "INSERT INTO `$tableName[0]` (`idx`, `Matrix`, `MatrixOrder`, `Date`) VALUES (NULL, '$Matrix', '$MOrder', '$today')";
                                                                $insert_2 = mysqli_query($conn, $queryInsertion) or die("Error: " . mysqli_errno($conn) . ": " . mysqli_error($conn));
                                                            ?>
                                                                <tr>
                                                                    <td class="border fs-5 text-center"> <?php echo $Matrix ?></td>
                                                                </tr>
                                                            <?php
                                                                $numUploadedM = $numUploadedM + 1;      // How many new matrix inserted in the table 
                                                            } else {        // What's The ERROR: No Old Matrix exist in table nor new matrix found :) 
                                                            ?>
                                                                <tr>
                                                                    <td class="border fs-5 text-center" colspan="4"> <?php echo "<span class='error border border-dark text-center mx-auto'>" . mysqli_errno($conn) . ": The Matrix \" $Matrix \" already exists in \" $tableName[0] \".</span><br/>"; ?> </td>
                                                                </tr>
                                                        <?php
                                                            }   //=== END IF (MYSQLI_QUERY($CONN, $QUERYINSERTION)) 
                                                            $lines++;
                                                        }   //===  END WHILE ($LINES < $LINESINFILE) 
                                                        ?>
                                                        <tr class="min-vw-100">
                                                            <td class="border fs-5 text-center border border-danger border-bottom-0" colspan="4">
                                            <?php
                                                echo "<br/><span class='text-info'>Note:</span> <span class='text-danger'>$numUploadedM </span>New Matrices of Order $MOrder Uploaded From '$filename' named file to $Status table '$tableName[0]' in database '$db_name'.";
                                                echo "</td></tr>";
                                            }
                                        }
                                        // @@@@@@@@@@@@@@@@@@@@@@@  END IF THE FILE NAME CONTAIN "CON" OR "DIS" THEN IT HAS HEIGHT @@@@@@@@@@@@@@@@@@@@@@@@@ \\
                                    } //=== END  foreach ($_FILES['mfile']['name'] as $key => $filename)
                                } else {
                                    echo "<div class='error border border-dark text-center mx-auto'> Error - " . mysqli_errno($conn) . ": Fields Must Not Be Empty.</div>";
                                } //=== END if(!empty($_FILES['mfiles']) && !empty($filename))
                                echo "</td></tr></tbody></table>";
                            } // END IF (ISSET($_POST['UPLOAD_1']))
                            echo "</tbody></table>";
                                            ?>
        </section>
    </main>
    <script src="./styles/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
<?php
        } ?>
</body>

</html>