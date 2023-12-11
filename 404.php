<?php // include "db_conn.php"; 
?>
<!-- Bootstrap 5.2  -->
<?php include "header.php"; ?>
     <title>Oops! Somthing went wrong</title>
</head>

<body>
     <!-- Styles Every HTML markups if possible. -->
     <header class="header-section">
          <!-- This is Header Part of the page  -->
          <?php include "menu.php"; ?>
     </header>
     <!-- This is content part of the page  -->
     <main class="content-wrapper py-5 w-50 mx-auto mt-5">
          <img src="./styles/assets/images/PD-logo@3x.png" alt="Poset Database Logo" class="mb-4 d-block mx-auto" width="150">
          <h4 class="fw-bolder">We apologize for the inconvenience. We are working to fix the issue.</h4>
          <h5 class="fw-bold mt-4">In the meantime, you can try: </h5>
          <ul class="">
               <!-- <li class="text-dark fst-normal lh-sm">Check Your Database Connection.</li> -->
               <li class="py-1 text-dark fst-italic lh-sm">Going To the <a href="./index.php" class="text-decoration-none text-bg-dark text-light px-2 py-2 rounded-1 text-nowrap"> Home Page </a></li>
               <li class="py-1 text-dark fst-italic lh-sm">Check Your Internet Connection.</li>
               <li class="py-1 text-dark fst-italic lh-sm">Check Your Firewall or Antivirus.</li>
               <li class="py-1 text-dark fst-italic lh-sm">Reload the page.</li>
          </ul>
          <p class="lh-lg fs-6">If the problem persists after trying all of these methods, please inform us by sending an email to <a href="mailto:salahuddin-mat@sust.edu" class="text-decoration-none text-bg-dark text-light px-2 py-2 rounded-1 text-nowrap">our email address</a> and try again later</p>
     </main>
     <?php include "footer.php" ?>