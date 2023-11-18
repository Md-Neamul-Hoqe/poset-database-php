<?php include "header.php"; ?>
<title>Poset Database</title>
</head>

<body class="bg-white text-dark">
     <header class="header-section">
          <!-- This is Main Menu Bar of the page  -->
          <?php include "menu.php"; ?>
     </header>
     <main class="content-wrapper pt-5 min-vh-100">
          <!-- Drawing Section -->
          <section id="drawing-section" class="px-3 mt-5">
               <div class="form">

                    <!-- Input the order of the matrix -->
                    <form id="PosetMatrix" style="max-width: 600px; margin-left: auto; margin-right: auto;" action="#" method="GET">
                         <label for="MOrder" class="form-label" hidden>Order Of The Matrix</label>
                         <select style="max-width: 100%;" class="form-select form-select-lg" name="morder" id="MOrder" data="<?php echo isset($_GET["morder"]) ? $_GET["morder"] : 5; ?>" autofocus>
                              <?php // include "db_conn.php"; 
                              ?>
                              <?php
                              /* Select all unique orders from Database "Posets" on Table 'allposets' */
                              $sqlQuery = "SELECT DISTINCT allposets.`MatrixOrder` FROM allposets";
                              $result = mysqli_query($conn, $sqlQuery) or die("Sorry. Required Data Not Found In Database." . mysqli_errno($conn) . ": " . mysqli_error($conn));
                              echo '<pre>';
                              print_r($sqlQuery);
                              echo '</pre>';
                              echo '<pre>';
                              print_r($result);
                              echo '</pre>';

                              // $num = $result->num_rows;        
                              while ($Orders = mysqli_fetch_assoc($result)) {
                                   echo "<option value='$Orders[MatrixOrder]' class='fs-5'>Order $Orders[MatrixOrder]</option>";
                              }
                              ?>
                         </select>
                         <input style="width: 150px;" class="px-0 mt-2 btn btn-outline-dark border-opacity-25 border" type="submit" name="submitOrder" value="Save Order">

                         <!-- Show Drawing Tool Button If Matrix Order is set. -->
                         <?php echo isset($_GET['morder']) ? '<a style="width: 150px;" class="btn btn-outline-dark float-end mt-2 border-opacity-25 border" href="#" data-bs-toggle="modal" data-bs-target="#DrawPoset" onkeydown="DrawingByKeyboard()">Draw For ' . $_GET["morder"] . '</a>' : null ?>
                    </form>
               </div>

               <!-- MODAL FOR POSET-MATRIX DRAWING -->
               <div class="modal fade" id="DrawPoset" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                         <div class="modal-content">
                              <div class="modal-header">
                                   <h3 class="modal-title" id="modalTitle">
                                        Drawing Tool
                                   </h3>
                                   <div class="ms-auto" id="IsKeyboard">Keyboard Feature is <span class="text-info">OFF</span>. <br> <span class="text-dark">Close the modal & press <kbd>Enter</kbd> to <span class="text-info">ON</span> this feature</span>.</div>
                                   <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                   <div class="container-fluid">
                                        <div class="row">
                                             <div class="col">
                                                  <!-- ####### Draw The Hesse Diagram of The Poset Matrix ########## -->
                                                  <div class="text-center" data-bs-content="Draw The Hesse Diagram of The Poset Matrix.">
                                                       <canvas id="poset" width="200" height="200" class="border border-4 border-dark shadow-lg p-0">
                                                            Sorry! Canvas Is Not Supported In Your Browser. Please Search manually.
                                                       </canvas>

                                                       <!-- Tab Feature's Status -->
                                                       <div class="ms-auto text-info" id="IsTab" hidden>Enter <kbd>Esc</kbd> to back to 'Drawing Tool'.</div>

                                                       <!-- Clear The Canvas -->
                                                       <input type="submit" onclick="resetCanvas()" value="Clear" class="position-absolute fs-6 fixed-right border-1 border-dark btn btn-light text-danger">

                                                       <!-- Learn How to draw -->
                                                       <a href="./drawingProcess.php" target="_blank" type="submit" class="position-absolute fs-6 fixed-right border-1 border-dark btn btn-light text-info">How To Draw?</a>

                                                       <!-- Submit The Poset -->
                                                       <input onclick="getMatrix()" name="submitPoset" type="submit" class="text-decoration-none text-light border btn btn-lg btn-dark" value="Submit">
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                              <div class="modal-footer">
                                   <div id="ShowErrors" class="error"></div>
                              </div>
                         </div>
                    </div>
               </div>
          </section>
          <section id="search-section" class="px-3 mt-5">

               <?php

               /* TABLE NAMES FROM DATABASE */
               $queryTable = "SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = 'Posets' AND TABLE_NAME <> 'users' LIMIT 3;";
               $sql = mysqli_query($conn, $queryTable);

               $tableName = [];
               while ($rows = mysqli_fetch_assoc($sql)) {
                    $tableName[] = $rows["TABLE_NAME"];
               }
               ?>

               <!-- Short List of The Poset Database -->
               <table id="searchedTable" class="mx-auto table table-striped-columns table-striped fw-semibold">
                    <thead>
                         <tr>
                              <td colspan="4" class="text-center fw-bold fs-1 pb-0">No. of Unlabeled Posets</td>
                         </tr>
                         <tr>
                              <th class="fs-5 fw-bolder text-center border border-dark px-3">No. of Elements</th>
                              <th class="fs-5 fw-bolder text-center border border-dark px-3">No. of Connected Posets</th>
                              <th class="fs-5 fw-bolder text-center border border-dark px-3">No. of Disconnected Posets</th>
                              <th class="fs-5 fw-bolder text-center border border-dark px-3">Total No. of Posets</th>
                         </tr>
                    </thead>
                    <tbody>
                         <?php
                         $nelements = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
                         $connected = [1, 1, 3, 10, 44, 238, 1650, 14512, 163341, 2360719, 43944974];
                         $disconnected = [0, 1, 2, 6, 19, 80, 395, 2487, 19890, 20655, 2804453];
                         $posets = [1, 2, 5, 16, 63, 318, 2045, 16999, 183231, 2567284, 46749427];

                         for ($i = 0; $i < count($nelements); $i++) {
                              echo "<tr>
                          <td>$nelements[$i]</td>
                          <td><a href='posetCounter.php?nthelements=$nelements[$i]&tableName=$tableName[1]&Total=$connected[$i]'>" . number_format($connected[$i]) . "</a></td>
                          <td><a href='posetCounter.php?nthelements=$nelements[$i]&tableName=$tableName[2]&Total=$disconnected[$i]'>" . number_format($disconnected[$i]) . "</a></td>
                          <td>" . number_format($posets[$i]) . "</td>
                      </tr>";
                         }
                         ?>
                    </tbody>
               </table>
          </section>
     </main>
     <footer class="big-footer">
          <script>
               morder = document.getElementById("MOrder").value = <?php echo isset($_GET["submitOrder"]) ? $_GET['morder'] : 5; ?>;

               /* Get transitive Closer matrix Then Search isomorphic Poset  */
               function getMatrix() {
                    let morder = <?php echo isset($_GET["morder"]) ? $_GET["morder"] : 0; ?>;

                    if (morder === coveringMatrix.length) {

                         var matrix = transitiveCloserMatrix();

                         let isomorphisms = isomorphicMatrices();
                         console.log('Isomorphic Posets: '),
                              console.table(isomorphisms);

                         /* Search with the matrix to search page (search.php) */
                         const url = "./search.php";
                         const data = {
                              Order: morder,
                              matrix: JSON.stringify(isomorphisms),
                              SEs: JSON.stringify(coveringMatrix),
                         };

                         function postToNewLocation(url, data) {
                              // Create a new form element
                              const form = document.createElement("form");
                              form.method = "POST";
                              form.action = url;

                              Object.keys(data).forEach((key) => {
                                   const input = document.createElement("input");
                                   input.type = "hidden";
                                   input.name = key;
                                   input.value = data[key];
                                   form.appendChild(input);
                              });

                              // Submit the form to the new location
                              document.body.appendChild(form);
                              form.submit();
                         }

                         // postToNewLocation(url, data);
                    } else {
                         document.getElementById("ShowErrors").innerHTML = "Please Select Exactly " + morder + " Elements.";
                         return false;
                    }
               }

               document.getElementById("ShowErrors").hidden = true; /* Hidden By Default */
               /* ========= Initializing The Variables =========== */
               const x = 50, // The gride start from x
                    radius = 5,
                    width = (document.getElementById("poset").width = (morder + 1) * x),
                    height = (document.getElementById("poset").height = (morder + 1) * x),
                    poset = document.getElementById("poset").getContext("2d");
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
                    SELength = 0,
                    coveringMatrix = [],
                    UTPoset = [],
                    ConnectedTo = [],
                    DistanceFromGrids = [],
                    XYPoints = [],
                    POSet = [];
          </script>
     </footer>

     <?php
     /* Drawing Tool */
     include "script.php";

     /* Footer with all footer links */
     include "footer.php";
     ?>

     <script>
          /* reDraw the poset-canvas after updating all arrays */
          reDraw();
     </script>