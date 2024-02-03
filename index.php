<?php
session_start();
include 'config.php';
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['register'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass'] );
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
   $select_user->execute([$name, $email]);

   if($select_user->rowCount() > 0){
      $message[] = 'Օգտանունը կամ էլփոստն արդեն գոյություն ունի!';
   }else{
      if($pass != $cpass){
         $message[] = 'Հաստատող գաղտնաբառը չի համապատասխանում!';
      }else{
         $insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
         $insert_user->execute([$name, $email, $cpass]);
         $message[] = 'Հաջողությամբ գրանցվել է, խնդրում ենք մուտք գործել հիմա!';
      }
   }

}

if(isset($_POST['update_qty'])){
   $cart_id = $_POST['cart_id'];
   $qty = $_POST['qty'];
   $qty = filter_var($qty, FILTER_SANITIZE_STRING);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$qty, $cart_id]);
   $message[] = 'Զամբյուղի քանակը թարմացվել է!';
}

if(isset($_GET['delete_cart_item'])){
   $delete_cart_id = $_GET['delete_cart_item'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_cart_id]);
   header('location:index.php');
}

if(isset($_GET['logout'])){
   session_unset();
   session_destroy();
   header('location:index.php');
}

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      $message[] = 'Խնդրում ենք նախ մուտք գործել!';
   }else{

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];
      $qty = filter_var($qty, FILTER_SANITIZE_STRING);

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
      $select_cart->execute([$user_id, $name]);

      if($select_cart->rowCount() > 0){
         $message[] = 'Արդեն ավելացված է զամբյուղում';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'Ավելացված է զամբյուղում!';
      }

   }

}

if(isset($_POST['order'])){

   if($user_id == ''){
      $message[] = 'Խնդրում ենք նախ մուտք գործել!';
   }else{
      $name = $_POST['name'];
      $name = filter_var($name, FILTER_SANITIZE_STRING);
      $number = $_POST['number'];
      $number = filter_var($number, FILTER_SANITIZE_STRING);
      $address = 'բնակարան '.$_POST['flat'].', '.$_POST['street'].' - '.$_POST['pin_code'];
      $address = filter_var($address, FILTER_SANITIZE_STRING);
      $method = $_POST['method'];
      $method = filter_var($method, FILTER_SANITIZE_STRING);
      $total_price = $_POST['total_price'];
      $total_products = $_POST['total_products'];

      $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);

      if($select_cart->rowCount() > 0){
         $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $method, $address, $total_products, $total_price]);
         $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
         $delete_cart->execute([$user_id]);
         $message[] = 'Պատվերը հաջողությամբ տեղակայվեց!';
      }else{
         $message[] = 'Ձեր զամբյուղը դատարկ է!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Պիցցա Խանութ</title>

   <!-- font awesome cdn հղում  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- ադմին css  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

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

<!-- վերնագրի բաժնի սկիզբը  -->

<header class="header">

   <section class="flex">

      <a href="#home" class="logo"><span>Պ</span>իցցա</a>

      <nav class="navbar">
         <a href="#home">Գլխավոր</a>
         <a href="#about">Մեր մասին</a>
         <a href="#menu">Մենյու</a>
         <a href="#order">Պատվեր</a>
         <a href="#faq">ՀՏՀ</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="order-btn" class="fas fa-box"></div>
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
         ?>
         <div id="cart-btn" class="fas fa-shopping-cart"><span>(<?= $total_cart_items; ?>)</span></div>
      </div>

   </section>

</header>

<!-- վերնագրի բաժնի վերջը -->

<div class="user-account">

   <section>

      <div id="close-account"><span>Փակել</span></div>

      <div class="user">
         <?php
            $select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
            $select_user->execute([$user_id]);
            if($select_user->rowCount() > 0){
               while($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>Բարի գալուստ ! <span>'.$fetch_user['name'].'</span></p>';
                  echo '<a href="index.php?logout" class="btn">logout</a>';
               }
            }else{
               echo '<p><span>Դուք դեռ մուտք չեք գործել!</span></p>';
            }
         ?>
      </div>

      <div class="display-orders">
         <?php
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if($select_cart->rowCount() > 0){
               while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
                  echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
               }
            }else{
               echo '<p><span>Ձեր զամբյուղը դատարկ է!</span></p>';
            }
         ?>
      </div>

      <div class="flex">

         <form action="user_login.php" method="post">
            <h3>Մուտք</h3>
            <input type="email" name="email" required class="box" placeholder="մուտքագրեք ձեր էլփոստը" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="մուտքագրեք ձեր գաղտնաբառը" maxlength="20">
            <input type="submit" value="Մուտք" name="login" class="btn">
         </form>

         <form action="" method="post">
            <h3>Գրանցվել</h3>
            <input type="text" name="name" oninput="this.value = this.value.replace(/\s/g, '')" required class="box" placeholder="մուտքագրեք ձեր օգտանունը" maxlength="20">
            <input type="email" name="email" required class="box" placeholder="մուտքագրեք ձեր էլփոստը" maxlength="50">
            <input type="password" name="pass" required class="box" placeholder="մուտքագրեք ձեր գաղտնաբառը" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="password" name="cpass" required class="box" placeholder="հաստատեք ձեր գաղտնաբառը" maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
            <input type="submit" value="Գրանցվել" name="register" class="btn">
         </form>

      </div>

   </section>

</div>

<div class="my-orders">

   <section>

      <div id="close-orders"><span>Փակել</span></div>

      <h3 class="title"> Իմ պատվերները </h3>

      <?php
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
         $select_orders->execute([$user_id]);
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){   
      ?>
      <div class="box">
         <p> տեղակայված է . <span><?=$fetch_orders['placed_on']; ?></span> </p>
         <p> անուն . <span><?= $fetch_orders['name']; ?></span> </p>
         <p> հեռախոսահամար . <span><?=$fetch_orders['number']; ?></span> </p>
         <p> հասցե . <span><?= $fetch_orders['address']; ?></span> </p>
         <p> վճարման եղանակը . <span><?=$fetch_orders['method']; ?></span> </p>
         <p> ընդհանուր պատվերներ . <span><?=$fetch_orders['total_products']; ?></span> </p>
         <p> ընդհանուր գինը : <span><?=$fetch_orders['total_price']; ?>֏/-</span> </p>
         <p> վճարման կարգավիճակը . <span style="color:<?php if($fetch_orders['payment_status'] == 'սպասվում է'){ echo 'red'; }else{ echo 'green'; }; ?>"><?=$fetch_orders['payment_status']; ?></span> </p>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">Դեռ ոչինչ պատվիրված չէ!</p>';
      }
      ?>

   </section>

</div>

<div class="shopping-cart">

   <section>

      <div id="close-cart"><span>Փակել</span></div>

      <?php
         $grand_total = 0;
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
              $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
              $grand_total += $sub_total; 
      ?>
      <div class="box">
         <a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('հեռացնել այս ապրանքը զամբյուղից?');"></a>
         <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
         <div class="content">
          <p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x <?= $fetch_cart['quantity']; ?>)</span></p>
          <form action="" method="post">
             <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
             <input type="number" name="qty" class="qty" min="1" max="99" value="<?= $fetch_cart['quantity']; ?>" onkeypress="if(this.value.length == 2) return false;">
               <button type="submit" class="fas fa-edit" name="update_qty"></button>
          </form>
         </div>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty"><span>Ձեր զամբյուղը դատարկ է!</span></p>';
      }
      ?>

      <div class="cart-total"> Ընդհանուր գումար .  <span><?=$grand_total; ?>֏/-</span></div>

      <a href="#order" class="btn">Պատվիրել</a>

   </section>

</div>

<div class="home-bg">

   <section class="home" id="home">

      <div class="slide-container">

         <div class="slide active">
            <div class="image">
               <img src="images/home-img-1.png" alt="">
            </div>
            <div class="content">
               <h3>Տնական պիցցա</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/home-img-2.png" alt="">
            </div>
            <div class="content">
               <h3>Պիցցա սնկով</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

         <div class="slide">
            <div class="image">
               <img src="images/home-img-3.png" alt="">
            </div>
            <div class="content">
               <h3>Ձիթապտուղ և սունկ</h3>
               <div class="fas fa-angle-left" onclick="prev()"></div>
               <div class="fas fa-angle-right" onclick="next()"></div>
            </div>
         </div>

      </div>

   </section>

</div>

<!-- մեր մասին բաժնի սկիզբը  -->

<section class="about" id="about">

   <h1 class="heading">Մեր մասին</h1>

   <div class="box-container">

      <div class="box">
         <img src="images/about-1.svg" alt="">
         <h3>Պատրաստված է սիրով</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum quae amet beatae magni numquam facere sit. Tempora vel laboriosam repudiandae!</p>
         <a href="#menu" class="btn">Մեր մենյուն</a>
      </div>

      <div class="box">
         <img src="images/about-2.svg" alt="">
         <h3>Առաքում 30 րոպեում</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum quae amet beatae magni numquam facere sit. Tempora vel laboriosam repudiandae!</p>
         <a href="#menu" class="btn">Մեր մենյուն</a>
      </div>

      <div class="box">
         <img src="images/about-3.svg" alt="">
         <h3>Կիսվել ընկերների հետ</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Illum quae amet beatae magni numquam facere sit. Tempora vel laboriosam repudiandae!</p>
         <a href="#menu" class="btn">Մեր մենյուն</a>
      </div>

   </div>

</section>

<!-- մեր մասին բաժնի վերջը -->

<!-- մենյու բաժնի սկիզբը  -->

<section id="menu" class="menu">

   <h1 class="heading">Մեր մենյուն</h1>

   <div class="box-container">

      <?php
         $select_products = $conn->prepare("SELECT * FROM `products`");
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){    
      ?>
      <div class="box">
         <div class="price"><?= $fetch_products['price'] ?>֏/-</div>
         <img src="uploaded_img/<?= $fetch_products['image'] ?>" alt="">
         <div class="name"><?= $fetch_products['name'] ?></div>
         <form action="" method="post">
            <input type="hidden" name="pid" value="<?= $fetch_products['id'] ?>">
            <input type="hidden" name="name" value="<?= $fetch_products['name'] ?>">
            <input type="hidden" name="price" value="<?= $fetch_products['price'] ?>">
            <input type="hidden" name="image" value="<?= $fetch_products['image'] ?>">
            <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            <input type="submit" class="btn" name="add_to_cart" value="Ավելացնել զամբյուղին">
         </form>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">Դեռ ոչ մի ապրանք չի ավելացվել!</p>';
      }
      ?>

   </div>

</section>

<!-- մենյու բաժնի վերջը -->

<!-- պատվերի բաժնի սկիզբը  -->

<section class="order" id="order">

   <h1 class="heading">պատվիրել հիմա</h1>

   <form action="" method="post">

   <div class="display-orders">

   <?php
         $grand_total = 0;
         $cart_item[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
              $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
              $grand_total += $sub_total; 
              $cart_item[] = $fetch_cart['name'].' ( '.$fetch_cart['price'].' x '.$fetch_cart['quantity'].' ) - ';
              $total_products = implode($cart_item);
              echo '<p>'.$fetch_cart['name'].' <span>('.$fetch_cart['price'].' x '.$fetch_cart['quantity'].')</span></p>';
            }
         }else{
            echo '<p class="empty"><span>Ձեր զամբյուղը դատարկ է!</span></p>';
         }
      ?>

   </div>

      <div class="grand-total"> ընդհանուր գինը . <span><?= $grand_total; ?>֏/-</span></div>

      <input type="hidden" name="total_products" value="<?= $total_products; ?>">
      <input type="hidden" name="total_price" value="<?= $grand_total; ?>">

      <div class="flex">
         <div class="inputBox">
            <span>ձեր անունը .</span>
            <input type="text" name="name" class="box" required placeholder="մուտքագրեք ձեր անունը" maxlength="20">
         </div>
         <div class="inputBox">
            <span>ձեր հեռախոսահամարը .</span>
            <input type="number" name="number" class="box" required placeholder="մուտքագրեք ձեր համարը" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;">
         </div>
         <div class="inputBox">
            <span>վճարման եղանակը</span>
            <select name="method" class="box">
               <option value="վճարում առաքման ժամանակ">վճարում առաքման ժամանակ</option>
               <option value="բանկային քարտ">բանկային քարտ</option>
               <option value="paytm">paytm</option>
               <option value="paypal">paypal</option>
            </select>
         </div>
         <div class="inputBox">
            <span>հասցեատող 01 :</span>
            <input type="text" name="flat" class="box" required placeholder="օր. հասցե1" maxlength="50">
         </div>
         <div class="inputBox">
            <span>հասցեատող 02 :</span>
            <input type="text" name="street" class="box" required placeholder="օր. հասցե2." maxlength="50">
         </div>
         <div class="inputBox">
            <span>փին կոդը .</span>
            <input type="number" name="pin_code" class="box" required placeholder="օր. 1501" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;">
         </div>
      </div>

      <input type="submit" value="Պատվիրել" class="btn" name="order">

   </form>

</section>

<!-- պատվերի բաժնի վերջը -->

<!-- հտհ բաժնի սկիզբը  -->

<section class="faq" id="faq">

   <h1 class="heading">ՀՏՀ</h1>

   <div class="accordion-container">

      <div class="accordion active">
         <div class="accordion-heading">
            <span>Ինչպես է դա աշխատում?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>որքան ժամանակ է պահանջվում առաքման համար?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>կարո՞ղ եմ պատվիրել մեծ երեկույթների համար?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

      <div class="accordion">
         <div class="accordion-heading">
            <span>որքան սպիտակուց է այն պարունակում?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>


      <div class="accordion">
         <div class="accordion-heading">
            <span>ձեթով է եփում?</span>
            <i class="fas fa-angle-down"></i>
         </div>
         <p class="accrodion-content">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam accusantium maxime, doloremque iusto deleniti veritatis quos.
         </p>
      </div>

   </div>

</section>

<!-- հտհ բաժնի վերջը -->

<!-- footer բաժնի սկիզբը  -->

<section class="footer">

   <div class="box-container">

      <div class="box">
         <i class="fas fa-phone"></i>
         <h3>հեռախոսահամարը</h3>
         <p>+374-456-7890</p>
         <p>+374-222-3333</p>
      </div>

      <div class="box">
         <i class="fas fa-map-marker-alt"></i>
         <h3>մեր հասցեն</h3>
         <p>սեվան, հայաստան - 1501</p>
      </div>

      <div class="box">
         <i class="fas fa-clock"></i>
         <h3>բացման ժամերը</h3>
         <p>09:00-ից մինչև 22:00-ն</p>
      </div>

      <div class="box">
         <i class="fas fa-envelope"></i>
         <h3>էլփոստի հասցեն</h3>
         <p>pizza@gmail.com</p>
         <p>pizza@mail.com</p>
      </div>

   </div>

   <div class="credit">
      &copy; Հեղինակային իրավունք @ <?= date('Y'); ?> by <span>VTSoft</span> | բոլոր իրավունքները պաշտպանված են!
   </div>

</section>

<!-- footer բաժնի վերջը -->

<!-- js ֆայլի հղումը  -->
<script src="js/script.js"></script>

</body>
</html>