        // Responsive Navbar Script 
        const toggle = document.getElementById('navToggle');
        const navWrapper = document.querySelector('.nav-wrapper');

        toggle.addEventListener('click', () => {
            navWrapper.classList.toggle('expanded');
        });