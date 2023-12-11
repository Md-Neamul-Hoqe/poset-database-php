<?php include "db_conn.php";
?>
<!-- Bootstrap 5.2  -->
<?php include "header.php"; ?>
    <title>Poset-Matrices || Order Matrix</title>
</head>

<body>
    <!-- Styles Every HTML markups if possible. -->
    <header class="header-section">
        <!-- This is Header Part of the page  -->
        <?php include "menu.php";
        if (isset($_SESSION['id']) && isset($_SESSION['user_name'])) { ?>
    </header>
    <!-- This is content part of the page  -->
    <main class="content-wrapper py-3">
        <!-- Sub section of main contents -->
        <!-- ================= TOTAL POSETS-COUNT TABLE ============= -->
        <section id="totalTable-search-section" class="my-5 py-5 px-3">
            <form action="<?php $_SERVER['PHP_SELF'] ?>" method="GET" class="form-control w-75 mx-auto">
                <label for="order">Enter The Order of The Matrix</label><input type="number" data-bs-toggle='tooltip' title="Enter The Order of The Matrix" name="order" id="Order" placeholder="<?php echo isset($_GET['search']) ? $_GET['order'] : 'Search by Matrix Order...' ?>">
                <input type="submit" name="search" value="Search . . ." placeholder="Search All Posets By Matrix Order." data-bs-toggle='tooltip' title="Search All Posets By Matrix Order.">
            </form>
            <br />
            <?php
            if (isset($_GET['search'])) {
                $order = $_GET['order'];
                $tableName = "allposets";
                $query = "SELECT * FROM $tableName WHERE MatrixOrder = $order";
                $result = mysqli_query($conn, $query) or die("<span class='error'> No " . $tableName . " Named Table Found. </span>");
                if (mysqli_num_rows($result) > 0) {
            ?>
                    <table id="searchedTable" class="mx-auto border-top border-3 border-danger mt-5 table-border">
                        <thead style="background-color: black; color: white;">
                            <th class="pb-1 fs-5 text-center bg-gradient bg-dark border border-dark" style="max-width: 300px;">Id</th>
                            <th class="pb-1 fs-5 text-center bg-gradient bg-dark border border-dark">Matrix Order</th>
                            <th class="pb-1 fs-5 text-center bg-gradient bg-dark border border-dark">Matrix</th>
                            <th class="pb-1 fs-5 text-center bg-gradient bg-dark border border-dark">Edit</th>
                            <th class="pb-1 fs-5 text-center bg-gradient bg-dark border border-dark">Delete</th>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                                <tr class="min-vw-100">
                                    <!-- Must be similar to the data base field name  -->
                                    <td class="border fs-5 text-center" style="min-width: 10px; text-align: center;"> <?php echo $row['idx'] ?> </td>
                                    <td class="border fs-5 text-center" style="min-width: 10px; text-align: center;"> <?php echo $row['MatrixOrder'] ?> </td>
                                    <td class="border fs-5 text-center" style="min-width: 10px; text-align: center;"> <?php echo "[" . $row['Matrix'] . "]" ?> </td>
                                    <td class="border fs-5 text-center" style="min-width: 10px; text-align: center;">
                                        <?php echo "<a class='bg-warning px-4 py-1 rounded-1 text-decoration-none border-0 text-dark' href='edit.php?action=update&id=" . $row['idx'] . "&morder=" . $row['MatrixOrder'] . "'>Edit</a>"; ?>
                                    </td>
                                    <td class="border fs-5 text-center">
                                        <?php echo "<a class='bg-danger py-1 px-2 rounded-1 text-decoration-none border-0 text-light' href='delete.php?action=delete&id=" . $row['idx'] . "'>Delete</a>"; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
        <?php } else {
                    echo "<span class='success-null'>No Matrix of Order $order Found in '$tableName'.</span>";
                }
            }

            mysqli_close($conn);
        } else {
            header("location: ./index.php");
        }
        ?>
        </section>
    </main>
    <script>
        /**
         *  Customized Basic Scripts [If Needed]
         */
        $("[data-bs-toggle='tooltip']").tooltip();
        const tooltipTriggerList = document.querySelectorAll("[data-bs-toggle='tooltip']");
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
</body>

</html>