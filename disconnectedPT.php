<?php

function disconnectedPosetsTable($tableName, $nelements, $Total)
{
    include "db_conn.php";
?>
    <table class="table table-striped-columns table-striped text-center">
        <thead>
            <tr class="text-center fs-3">
                <th colspan="<?= $nelements + 1; ?>">No. of Unlabeled Disconnected Posets With <?= $nelements ?> Elements</th>
            </tr>
        </thead>
        <?php

        /* For Disconnected Posets */
        $n =  0; // Indexing (Header) of DirectTerms
        for ($Height = 0; $Height <= $nelements + 1; $Height++) { // DirectTerms loop $Height
            echo "<tr>"; ?>
            <script>
                /* Initialize Before Count For DirectTerms */
                totalPosetsForDirectTerms = 0;
            </script>
            <?php
            for ($j = 0; $j < 2; $j++) {
                if ($j === 0) {
                    if ($Height === 0) {
                        echo "<th class='text-center fs-5'>No.&nbsp;of&nbsp;Direct&nbsp;Terms</th>";
                    } else if ($Height === $nelements + 1) {
                        echo "<th class='text-center fs-5'>Total</th>";
                    } else {
                        /* Header For DirectTerms in 1st column */
                        echo "<th class='text-center fs-5'>";
                        echo $n = $Height;
                        echo "</th>";
                    }
                } else {
                    if ($Height === 0) {
                        echo "<th class='text-center fs-5'>No. of Posets</th>";
                    } else if ($Height === $nelements + 1) {
                        echo "<td>$Total</td>";
                    } else {
                        /* Fill The Inner Cells*/
                        // SELECT * FROM `disconnposets` WHERE `disconnposets`.`MatrixOrder` = 5 AND `disconnposets`.`Height` = 2 LIMIT 5, 5;
                        $numElements = "SELECT $tableName.`Matrix` FROM `$tableName` WHERE `MatrixOrder` =  $nelements && `Height` = $n";

                        $resultE = mysqli_query($conn, $numElements) or die("<p class='error'>Please try again later. We are working hard to update our database.</p>");
                        $numE = $resultE->num_rows;

                        echo "<td><a id='D-$n' href='search.php?Order=$nelements&Height=$n&Table=$tableName' class='text-decoration-none'>$numE</a></td>";
            ?>
                        <script>
                            /* Save the value for DirectTerms */
                            var save1 = document.getElementById('<?php echo "D-$n" ?>').innerText;
                            totalPosetsForDirectTerms += parseInt(save1, 10);
                        </script>
    <?php
                    }
                }
            }   /* Column ($j) END */
            echo "</tr>";
        } /* Row ($Height) End */
        echo "</table>";
        mysqli_close($conn);
    }

    ?>