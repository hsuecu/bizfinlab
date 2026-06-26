(function (Drupal, once) {
  Drupal.behaviors.mobileMenu = {
    attach(context) {
      once('mobile-menu', '.menu-toggle', context).forEach((button) => {
        const menu = document.querySelector('.region-header .block-menu');

        if (!menu) {
          return;
        }

        button.addEventListener('click', () => {
          menu.classList.toggle('open');
        });
      });
    }
  };
})(Drupal, once);