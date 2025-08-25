        document.addEventListener('DOMContentLoaded', function () {
            const menuBtn = document.getElementById('menu-toggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            menuBtn.addEventListener('click', function () {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            });

            overlay.addEventListener('click', function () {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        });