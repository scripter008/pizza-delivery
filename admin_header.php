<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<header class="header">

   <section class="flex">
      <a href="admin_page.php" class="logo">Ադմին<span>Վահանակ</span></a>

      <nav class="navbar">
         <a href="admin_page.php">Գլխավոր</a>
         <a href="admin_products.php">Ապրանքներ</a>
         <a href="admin_orders.php">Պատվերներ</a>
         <a href="admin_accounts.php">Ադմին</a>
         <a href="users_accounts.php">Օգտվող</a>		 
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?=@$fetch_profile['name']; ?></p>
         <a href="admin_profile_update.php" class="btn">Թարմացնել պրոֆիլը</a>
		 <a href="logout.php" class="delete-btn">Ելք</a>
            <div class="flex-btn">
            </div>
      </div>
   </section>

</header>