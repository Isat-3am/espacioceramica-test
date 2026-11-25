<header>
  <nav class="navbar navbar-expand-xxl navbar-dark py-3 mt-3 p-3 p-md-4 ps-lg-5 pe-lg-5">
    <div class="container-fluid p-0">
      <a class="navbar-brand text-primary" href="#">
        <div class="d-none d-sm-none d-md-block">
          <h1><strong><?php echo $_SESSION['client'] ?> Backend</strong></h1>
        </div>
        <div class="d-block d-sm-block d-md-none">
          <h1><strong><?php echo $_SESSION['client'] ?> Backend</strong></h1>
        </div>
      </a>
      <button class="rounded-2 navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link <?php if ($sec == 1) echo "active"; ?>" href="products">Productos</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php if ($sec == 4) echo "active"; ?>" href="horns">Hornos</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?php if ($sec == 2) echo "active"; ?>" href="about">Nosotros</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php if ($sec == 3) echo "active"; ?>" href="voluntarios">Residentes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php if ($sec == 7) echo "active"; ?>" href="presentaciones">Presentaciones</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php if ($sec == 8) echo "active"; ?>" href="residencias">Residencias<br>y Exp. Formativas</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php if ($sec == 9) echo "active"; ?>" href="newsletter">Newsletter</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php if ($sec == 5) echo "active"; ?>" href="galleries">Galerias</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-primary link-primary" target="_blank" rel="noopener" href="https://espacioceramica-test.vercel.app">Web</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-primary link-primary" href="logout">Cerrar sesión</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>
