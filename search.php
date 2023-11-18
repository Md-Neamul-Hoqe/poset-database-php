<?php
include "header.php";

$MOrder = isset($_POST["Order"]) ? $_POST["Order"] : (isset($_GET["Order"]) ? $_GET["Order"] : 5);

?>
<title>Search Posets</title>
</head>
<script>
    const x = 50,
        morder = <?= $MOrder ?>,
        radius = 5;
    /* ========= Initializing The Variables =========== */
    var xCoord = 0,
        yCoord = 0,
        type = "Connected",
        PxCurrent = 0,
        PyCurrent = 0,
        PStart = -1,
        PEnd = -1,
        isHovered = false,
        keyPosY = 0,
        keyPosX = 0,
        Draw = 0,
        reset = false,
        SELength = 0, // Array To Store The Number of Selected Nodes
        coveringMatrix = [], // Array To Store The Selected Nodes
        UTPoset = [],
        ConnectedTo = [],
        DistanceFromGrids = [], // Distance From Mesh Points To Mouse Selection
        XYPoints = [], // Mesh Points with max info
        POSet = [];
    // console.table(coveringMatrix);
    /* ========= Initialization of Canvas =========== */
</script>
<?php
include "script.php";
?>

<body class="bg-white-50 text-dark">
    <header class="header-section">
        <!-- This is The Main Menu Bar of the page  -->
        <?php include "menu.php"; ?>
    </header>

    <!-- To Back / Top Buttons  -->
    <a action="action" class="bg-warning bg-opacity-25 rounded-5" href='#' onclick="history.back()" value="Back" id="backPage"><img src="./styles/assets/images/backPage.png" alt="Back To Previous Page"></a>
    <a class="bg-success bg-opacity-25 rounded-5" href="#" id="myBtn"><img title='Poset-Matrix' src="./styles/assets/images/backToTop.png" alt="Back To Top"></a>

    <main class="content-wrapper pt-5 mt-5 min-vh-100">
        <?php
        /**
         * ####################################################
         * Search By Drawing Tool
         *  ###################################################
         */


        if (isset($_POST["matrix"]) && isset($_POST["SEs"])) {
            /* Get The Matrix / Poset line From Drawing Tool */

            $matrices = json_decode($_POST["matrix"]);
            $SelectedElements = json_decode($_POST["SEs"]);
            $PosetName = "The";        // Allposets = The Posets
            foreach ($matrices as $Matrix) {
                $Matrix = implode(' ', $Matrix);

                $stmt_1 = "SELECT `allposets`.`Matrix`, `allposets`.`Type` FROM `allposets` WHERE `Matrix` = '$Matrix'";
                $result = mysqli_query($conn, $stmt_1);
                ($result) ? ($num = mysqli_num_rows($result)) : die("<div class='error'>Error-" . mysqli_errno($conn) . ": Error - " . mysqli_error($conn) . "</div>");

                if ($num > 0) break; // if found then break
            } // end foreach()

            echo '<div id="searchedTable" class="mx-auto mt-5">';
            if ($num > 0) {
                echo '<div class="row"><div class="col text-center fs-3">';
                echo "The exact match for your search by &lt;'Drawing&nbsp;Tool'&gt;";
                echo '</div></div><div id="TableBody" class="row"><div class="col text-center">';

                /* Show Every Poset's Matrix With it's Hasse Diagram  */
                $row = mysqli_fetch_all($result);
                $type = ($row[0][1] === 'con') ? 'Connected' : 'Dis-Connected';
                $table = ($type === 'Connected') ? 'connposets' : 'disconnposets';
                $stmt_1 = "SELECT `Matrix`, `Height`, `Width` FROM `$table` WHERE `Matrix` = '$Matrix'";
                $getHeightWidth = mysqli_query($conn, $stmt_1);

                if (!$getHeightWidth) {
                    echo "<div class='error'>Please Upload $type Poset-Matrix. <br/> There are no matrices related to $type-posets.</div>";
                } else {
                    $HeightWidth = mysqli_fetch_assoc($getHeightWidth);

                    $Height = (isset($HeightWidth["Height"]) && $HeightWidth) ? $HeightWidth["Height"] : 0;
                    $Width = (isset($HeightWidth["Width"]) && $HeightWidth) ? $HeightWidth["Width"] : 0;
                    echo $Height, $Width;
                }
                echo '<div class="row"><div class="col fs-5 mt-3">';
                /* Header of The Poset */
                echo "<span class='fs-6'>The Height is ", ((isset($Height) && $Height > 0) ? $Height : $Height = 'Not Identified') . "&nbsp;&&nbsp;Width&nbsp;is&nbsp;" . ((isset($Width) && $Width > 0) ? $Width : $Width = 'Not Identified'), " of the poset.</span>";

                /* Poset Line To Matrix Form */
                connMatrixConstruction($MOrder, $Height, $Width, $row[0][0], $PosetName);

                echo "<hr></div></div></div>";
            } else {
                echo "<div class='error mt-5'>Sorry! There have no such kind of poset exist.</div>";
            } // IF CONDITION END

            echo '</div></div>';
        } else if (isset($_GET["Height"]) || isset($_GET["Width"])) { // update || to && after adding width to the database 
            /**
             * ####################################################
             * Search Manually
             * ####################################################
             */

            $table = $_GET["Table"];
            if ($table == "connposets") {
                echo "Connected Posets";
                $Height = $_GET["Height"] | 0;
                $Width = $_GET["Width"] | 0;

                $PosetName = "Connected";

                $stmt_1 = "SELECT `$table`.Matrix FROM `$table` WHERE MatrixOrder = $MOrder AND Height = $Height AND Width = $Width ORDER BY `$table`.Matrix ASC";
            } else if ($table == "disconnposets") {
                echo "Dis-Connected Posets";
                $Height = $_GET["Height"] | 0;

                $PosetName = "Dis-Connected";

                $stmt_1 = "SELECT `$table`.Matrix FROM `$table` WHERE MatrixOrder = $MOrder AND Height = $Height ORDER BY `$table`.Matrix ASC";
            } else {
                echo "All Posets";
                $PosetName = "All";
                $stmt_1 = "SELECT `$table`.Matrix FROM `$table` WHERE MatrixOrder = $MOrder AND Height = $Height OR Width = $Width ORDER BY `$table`.Matrix ASC";
            }
            // $searchBoxShow = true;
            $result = mysqli_query($conn, $stmt_1) or die('<p class="error">Error no.-' . mysqli_errno($conn) . ': ' . mysqli_error($conn) . '</p>');
            $num = mysqli_num_rows($result);

        ?>
            <!-- ############################################### 
                Search Interval 
                ############################################### -->

            <p class="bg-info text-dark bg-opacity-25 text-center rounded fs-6 fw-bolder py-2">Take A Short Range To Convenient Search</p>
            <form id="IntervalForm" action="<?php $_SERVER['PHP_SELF']; ?>" method="POST" class="d-flex border-bottom border-5 border-dark mb-3 rounded-4">
                <label for="min" hidden>Enter The Min Index No.</label>
                <input id="Min" class="form-control me-2" placeholder="<?php echo isset($_POST['MIN']) ? $_POST['MIN'] : 'Search From 0'; ?>" type="search" name="MIN" id="searchMin">
                <label for="max" hidden>Enter The Max Index No.</label>
                <input id="Max" class="form-control" placeholder='<?php echo isset($_POST["MIN"]) ? $_POST["MAX"] : "Search Up To $num"; ?>' type="search" name="MAX" id="searchMax">
                <input class="ms-2" type="submit" value="Search...">
            </form>
            <?php

            echo '<section id="SearchResults">';
            $Start = 1;
            $Interval = 10;

            $MatrixNo = (isset($_POST['MIN']) and $_POST['MIN'] <> 0) ? $_POST['MIN'] : $Start;      /* Index Start From 1 */

            $Max = (isset($_POST['MAX']) and $_POST['MAX'] <= $num) ? $_POST['MAX'] : (($num > $Interval) ? $Interval : $num); // Don't show all only first 30, if try to see all.

            if ($num > 0) {
                echo '<div id="searchedTable" class="mx-auto mt-5"><div class="row"><div class="col text-center fs-3">';
                echo "<h1 class='fs-3'>$PosetName Poset-Matrices For $MOrder Elements";
                echo (isset($Height) && $Height > 0) ? " With&nbsp;Height&nbsp;" . $Height : '';
                echo ((isset($Width) && $Width > 0) ? "&nbsp;&&nbsp;Width&nbsp;" . $Width . "</h1>" :  "</h1>");

                echo '</div></div><div id="TableBody" class="row"><div class="col text-center">';

                /* Show Every Poset's Matrix With it's Hasse Diagram */
                $row = mysqli_fetch_all($result);

                while ($MatrixNo <= $Max) {
                    echo '<div class="row"><div class="col fs-5 mt-3">';
                    /* Set Superscript on Number place */
                    switch ($MatrixNo % 10) {
                        case 1:
                            $place = 'st';
                            break;
                        case 2:
                            $place = 'nd';
                            break;
                        case 3:
                            $place = 'rd';
                            break;
                        default:
                            $place = 'th';
                            break;
                    }

                    echo $MatrixNo . "<sup>" . $place . "</sup> Matrix <hr style='border: 2px solid; border-radius: 100%;'/>";

                    connMatrixConstruction($MOrder, $Height, $Width, $row[$MatrixNo - 1][0], $PosetName);

                    echo "<hr>";
                    if ($MatrixNo === $Max and $Max < $num) {
            ?>
                        <!-- <button onclick="SearchNext10(-1)" class='border border-3 border-success p-2 rounded'>&#8810;</button> << -->
                        <button type="button" onclick="SearchPrev10()" class='border border-3 border-success p-2 rounded'>&#8810;</button> <!-- >> -->
                        <button type="button" onclick="SearchNext10()" class='border border-3 border-success p-2 rounded'>&#8811;</button> <!-- >> -->
                        <script>
                            /**
                             * Show Next 10 Posets On Click The Button
                             */
                            function SearchPrev10() {

                                document.getElementById('Min').value = "<?php echo $MatrixNo ?>";
                                document.getElementById('Max').value = "<?php echo ($MatrixNo + $Interval > $num) ? $num : ($MatrixNo + $Interval) ?>";
                                document.getElementById("IntervalForm").submit();
                            }

                            console.log(x);

                            function SearchNext10() {
                                if (x <= 0) {
                                    return;
                                }

                                <?php
                                if (isset($_POST["Min"])) {
                                    $_POST["Min"] = $MatrixNo;
                                }
                                if (isset($_POST["Max"])) {
                                    $_POST["Max"] = ($MatrixNo + $Interval > $num) ? $num : $MatrixNo + $Interval;
                                }
                                ?>

                                document.getElementById('Min').value = "<?php echo $MatrixNo ?>";
                                document.getElementById('Max').value = "<?php echo ($MatrixNo + $Interval > $num) ? $num : ($MatrixNo + $Interval) ?>";
                                document.getElementById("IntervalForm").submit();
                            }
                        </script>
        <?php
                        if ($num < $Max) {
                            echo "<br><div class='border border-3 p-3 rounded'>No More Posets Found.</div>";
                        }
                    }
                    $MatrixNo++;
                    echo '</div></div>';
                } // WHILE LOOP CLOSEED
            } else {
                echo "<div class='error mt-5'>Sorry, there is no poset of that kind. Please try another one.</div>";
            } // IF CONDITION END
            echo '</div></div></div>';
        }

        ?>

        </section>
    </main>

    <footer class="big-footer">
        <!-- Calling Functions  -->
        <?php
        ####################### MATRIX CONSTRUCTOR #################
        function connMatrixConstruction($MOrder, $Height, $Width, $Matrix, $PosetName)
        {
            $k = 0;
            $Matrix = str_replace(' ', '', $Matrix);  // To remove the space between two elements in the matrix saved in the database.
            // echo "<div class='border-start border-end border-5 border-dark m-5 col'>";
        ?>

            <div class='border border-1 row'>
                <!-- Two Equal Column [Matrix & Hasse Diagram] -->
                <div class='border-end col-6 d-flex justify-content-center align-items-center'>
                    <!-- Poset Matrix -->
                    <div class='row m-4'>
                        <!-- Matrix Notation [ -->
                        <div id="matrix-<?= $Matrix ?>" class='col border-start-end mx-auto h-100 position-relative' style='min-width: <?= $MOrder * 40 ?>px; max-width: <?= $MOrder * 40 + 30 ?>px;'>
                            <!-- Matrix Rows -->
                            <?php for ($i = 0; $i < $MOrder; $i++) { ?>
                                <div class='row m-auto fs-5' style='width: <?= $MOrder * 40 ?>px'>
                                    <!-- Matrix Row -->
                                    <?php
                                    for ($j = 0; $j < $MOrder; $j++) {
                                    ?>
                                        <div class='col'>
                                            <?php
                                            if ($i == $j) {
                                                echo "1";
                                            } elseif ($i < $j) {
                                                echo $Matrix[$k++];
                                            } else {
                                                echo "0";
                                            }
                                            ?>
                                        </div> <!-- col END -->
                                    <?php
                                    }
                                    ?>
                                </div> <!-- one matrix row end || row END -->
                            <?php
                            } // FOR LOOP CLOSED FOR POSET-MATRIX
                            ?>
                        </div> <!-- Matrix Notation ] || col END-->
                    </div> <!-- row END -->
                </div> <!-- col-6 END -->

                <!-- ============================================= 
                    Hasse diagram corresponding to the above poset 
                    ============================================= -->
                <div class='p-2 w-50 col-6 m-auto'>
                    <?php if (isset($_GET["matrix"]) && isset($_GET["SEs"])) {
                    ?>
                        <!-- Draw The Poset Which is just Inputed to search -->
                        <div class="text-center d-flex justify-content-center" data-bs-content="Draw The Hasse Diagram of The Poset Matrix.">
                            <canvas id="poset-Draw" width="200" height="200" class="border border-4 border-dark shadow-lg p-0">
                                Sorry! Canvas Is Not Supported In Your Browser. Please Search manually.
                            </canvas>
                        </div>
                        <script>
                            var PosetId = document.getElementById("poset-Draw"),
                                poset = PosetId.getContext("2d"),
                                width = (PosetId.width = (morder + 1) * x),
                                height = (PosetId.height = (morder + 1) * x);
                            PosetId.className = "border-0";
                            poset.rect(x - 12, x - 12, width - x * 1.5, height - x * 1.5);
                            poset.clip();
                            reDraw();
                        </script>
                    <?php
                        // return;
                    } else {
                    ?>
                        <a href='#' data-bs-toggle="modal" data-bs-target="#PosetMatrix-<?php echo $Matrix; ?>"><img title='Poset-Matrix' src='<?php echo $dir = "./styles/assets/images/No-image-found.jpg"; ?>' alt='Poset-Matrix Figure Goes Here.' width='150'></a>
                    <?php } ?>
                </div> <!-- Hasse Diagram END -->

                <script>
                    var matrixBeforeAfter = document.querySelector("#matrix-<?= $Matrix ?>");
                    /* Set The Size Of The Paranthisis (Matrix Notation) According To Matrix Size */
                    if (matrixBeforeAfter) {
                        matrixBeforeAfter.style.setProperty("--morder-font-size", "<?php echo $MOrder * 35 ?>px");
                    }
                </script>

                <!-- ################ MODAL FOR POSET-MATRIX FIGURE ############## -->
                <div class="modal fade" id="PosetMatrix-<?= $Matrix; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="PosetMatrixLabel-<?php echo $Matrix; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="PosetMatrixLabel-<?php echo $Matrix; ?>">
                                    <!-- Height & Width Of the Poset-Matrix Shown Here in Later -->
                                    <?php echo "$PosetName Poset-Matrix For $MOrder Elements With&nbsp;Height&nbsp;" . (($Height > 0) ? $Height : 'Not Identified') . "&nbsp;&&nbsp;Width&nbsp;" . (($Width > 0) ? $Width : 'Not Identified'); ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="container-fluid">
                                    <div class="row">
                                        <!-- ####### Show The Hasse Diagram Of The Matrix ########## -->
                                        <div class="col"><img title='Poset-Matrix' src='<?php echo $dir; ?>' alt='Poset-Matrix Figure Goes Here.' title="Popover title" data-bs-content="Popover body content is set in this attribute."></a></div>
                                    </div>
                                </div>
                            </div> <!-- Modal Body END -->
                        </div> <!-- Modal Content END -->
                    </div> <!-- Modal Dialog END -->
                </div> <!-- Modal END -->
            </div> <!-- row End || [Matrix & Hasse Diagram] Shown-->
        <?php
        } // connMatrixConstruction() END
        ?>
    </footer>
    <!-- Footer -->
    <?php
    include "footer.php";
    ?>