<nav class="navbar navbar-expand navbar-light navbar-bg">
    <a class="sidebar-toggle js-sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>

    <div class="navbar-collapse collapse">
        <ul class="navbar-nav navbar-align">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                <?php   if(isset($_SESSION['myname']))
                  {
                    echo "<span class='text-dark'>" . $_SESSION['myname'] . "</span>";
                     ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item" href="http://159.203.110.60//order/php/user/logout.php">Log out</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
