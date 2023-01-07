<?php include "header.php"; ?>
<title>Poset Database</title>
</head>

<body class="bg-white text-dark">
     <!-- Styles Every HTML markups if possible. -->
     <header class="header-section">
          <!-- This is Header Part of the page  -->
          <?php include "menu.php"; ?>
     </header>
     <main class="content-wrapper pt-5 min-vh-100">
          <!-- Sub section of main contents -->
          <!-- ================= TOTAL POSETS-COUNT TABLE ============= -->
          <section id="drawing-section" class="px-3 mt-5">
               <!-- Set The Matrix Order / Number of the Poset Elements -->
               <h1>Drawing Process</h1>
               <div class="mouseUsed">
                    <h3>Drawing By Mouse-Click</h3>
                    <p>
                         <ul>
                              <li>The bottom-left most element is assumed as first element & sequencially top-right most element as last element.</li>
                              <li>To select/fill click on small circles. If already seleceted/filled then it will become unselected/unfilled.</li>
                              <li>Click between two selected nodes/elements to connect/relate them. If already connected then it will removed.</li>
                              <li>By this process fill exectly the number of elements as you select the order of the poset.</li>
                              <li>After completing the drawing select submit button to show the poset matrix found similar poset in our database. Or you can clear the drawing canvas by clicking the clear button.</li>
                              <li>To close the drawing canvas click on 'Close' button in the top right corner.</li>
                         </ul>
                    </p>
               </div>
               <div class="keyboardUsed">
                    <h3>Drawing By Keyboard-Press</h3>
                    <p>
                         <ul>
                              <li>The bottom-left most element is assumed as first element & sequencially top-right most element as last element.</li>
                              <li>To move the caret/curser use <kbd>left-right-top-bottom</kbd> arrows in your keyboard.</li>
                              <li>To select/fill press <kbd>Enter</kbd>. If already seleceted/filled then it will become unselected/unfilled.</li>
                              <li>To connect/relate them, press <kbd>Shift + Enter</kbd> on the first element then move the caret to the next element and again press <kbd>Shift + Enter</kbd>. If already connected then it will removed.</li>
                              <li>By this process fill exectly the number of elements as you select the order of the poset.</li>
                              <li>After completing the drawing press <kbd>Tab</kbd> to move the caret to Close, Clear or Submit button the press <kbd>Enter</kbd> to close, clear canvas or submit.</li>
                              <li>To back to the canvas press <kbd>Esc</kbd> button.</li>
                         </ul>
                    </p>
               </div>

          </section>
          <!-- ############################### OUTPUT Table Details ######################################### -->
     </main>
     <?php
     /* Footer with all footer links */
     include "footer.php";
     ?>