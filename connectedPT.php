<?php
/* Search All Posets With Height For Certain Order -> index.php */
function connectedPosetsTable($tableName, $nelements, $Total)
{
    include "db_conn.php";
?>
    <table class="table table-striped-columns table-striped text-center">
        <thead>
            <tr class="text-center fs-3">
                <th colspan="<?= $nelements + 1; ?>">No. of Unlabeled Connected Posets With <?= $nelements ?> Elements</th>
            </tr>
        </thead>
        <?php
        /* Connected Posets */
        $n = 0; // Indexing (Header) of Height
        for ($i = 0; $i <= $nelements; $i++) {  // Height loop $i
            echo "<tr>"; ?>
            <script>
                /* Initialize Before Count For Height */
                totalPosetsForHeight = 0;
            </script>
            <?php
            for ($j = 0; $j <= $nelements; $j++) {  // Diameter loop $j
                if ($i == 0) {

                    /* Header for Diameter */
                    if ($j == 0) {
                        echo "<th id='widthHight' class='text-center fs-5'><canvas id='Diagonal' class='bg-transparent border-0' width='100%' height='100%'></canvas><span>Height&nbsp;&Downarrow; </span><span>Diameter&nbsp;&Rightarrow;</span></th>";
                    } else if ($j == $nelements) {
                        echo "<th class='text-center fs-5'>Total</th>";
                    } else {
                        echo "<th class='text-center fs-5'>$j</th>"; ?>
                        <script>
                            /* Initialize Before Count For Diameter */
                            var totalPosetsForWidth_<?= $j ?> = 0;
                        </script>
                    <?php
                    }
                    /* Header for Diameter END */
                } else if ($j == 0 && $i != $nelements) {

                    /*Header For Height */
                    echo "<th class='text-center; fs-5'>";
                    echo $n = $i + 1;
                    echo "</th>";
                } else if ($j == 0) {
                    echo "<th class='text-center; fs-5'>Total</th>";
                    /*Header For Height END */
                } else if ($i < $nelements && $j < $nelements && $j > 0) {

                    /* Fill The Inner Cells*/
                    // echo $tableName, "<br>", $nelements, "<br>", $n, "<br>", $j;

                    $numElements = "SELECT $tableName.`Matrix` FROM `$tableName` WHERE `MatrixOrder` =  $nelements && `Height` = $n && `Width` = $j";
                    $resultE = mysqli_query($conn, $numElements) or die("Some Error Found.");
                    $numE = $resultE->num_rows;
                    // echo '<pre>';
                    // print_r($numE);
                    // echo '</pre>';

                    echo "<td><a id='$n-$j' href='search.php?Order=$nelements&Height=$n&Width=$j&Table=$tableName' class='text-decoration-none'>$numE</a></td>";
                    ?>
                    <script>
                        /* Save the value for Height */
                        var save1 = document.getElementById('<?php echo "$n-$j" ?>').innerText;
                        totalPosetsForHeight += parseInt(save1, 10); // 'save1' string to integer  as decimal number

                        /* Save the value for Diameter */
                        var save2_<?= $j ?> = document.getElementById('<?php echo "$n-$j" ?>').innerText;
                        totalPosetsForWidth_<?= $j ?> += parseInt(save2_<?= $j ?>, 10);
                    </script>
                <?php
                    // mysqli_close($conn);
                } else if ($i == $nelements && $j < $nelements) { ?>
                    <td class='text-center;' id='<?= "TW-$n-$j" ?>'><?= "TW" ?></td>
                    <script>
                        document.getElementById('<?= "TW-$n-$j" ?>').innerHTML = totalPosetsForWidth_<?= $j ?>;
                    </script>
                <?php
                } else if ($j == $nelements && $i < $nelements) {
                    echo "<td class='text-center;' id='TH-$n-$j'>" . 'TH' . "</td>"; ?>
                    <script>
                        document.getElementById('<?= "TH-$n-$j" ?>').innerHTML = totalPosetsForHeight;
                    </script>
    <?php
                } else {
                    echo "<td>$Total</td>";
                }
            }   /* Column ($j) END */
            echo "</tr>";
        } /* Row ($i) End */
        echo "</table>";
        mysqli_close($conn);
    } // CLOSED function connectedPosetsTable($tableName, $nelements)
