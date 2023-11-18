<?php
include "header.php";
?>

<title>List of Unlabeled Connected Posets</title>
</head>

<body class="bg-white-50 text-dark">
    <header class="header-section">
        <?php include "menu.php"; ?>
    </header>

    <a action="action" class="bg-warning bg-opacity-100 rounded-5" href='#' onclick="history.back()" value="Back" id="backPage"><img src="./styles/assets/images/backPage.png" alt="Back To Previous Page"></a>

    <main class="content-wrapper mt-5 pt-5 min-vh-100">
        <!-- ================= TOTAL POSETS-COUNT TABLE ============= -->
        <section id="search-section" class="px-3 mt-5">
            <!-- ############################### SEARCHED RESULT / OUTPUT Counter Table ######################################### -->
            <?php
            $tableName = $_GET['tableName'];
            $nelements = $_GET['nthelements'];
            $Total = $_GET['Total'];

            /* functions */
            include "connectedPT.php";
            include "disconnectedPT.php";

            if ($tableName === "disconnposets") {
                // print_r($tableName);
                disconnectedPosetsTable($tableName, $nelements, $Total);
            } else {
                connectedPosetsTable($tableName, $nelements, $Total);
            }
            ?>
        </section>

    </main>
    <script>
        myDiag = document.getElementById("Diagonal").getContext("2d");
        width = myDiag.width;
        height = myDiag.height;

        myDiag.moveTo(0, 0);
        myDiag.lineTo(100, 100);
        myDiag.stroke();
    </script>
    <footer class="big-footer">
        <!-- This is footer part of the page  -->
        <?php include "footer.php"; ?>
    </footer>