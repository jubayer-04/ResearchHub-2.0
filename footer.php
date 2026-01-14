
<footer class="footer">
    <div class="footer-container">

        <!-- LEFT -->
        <div class="footer-left">
            <p>
                Created by 
                <a href="https://t.me/jubaye_r" target="_blank" class="footer-link">
                    Jubayer Hossain
                </a>
            </p>
            <p>© <?php echo date('Y'); ?> ResearchHub. All rights reserved.</p>
        </div>

        <!-- CENTER -->
        <div class="footer-center">
            <p><strong>Contact</strong></p>
            <p>
                <i class="fa-solid fa-envelope"></i>
                <a href="mailto:jubayerhr@gmail.com" class="footer-link">
                    jubayerhr@gmail.com
                </a>
            </p>
            <p>
                <i class="fa-solid fa-phone"></i>
                <a href="tel:+8801625305856" class="footer-link">
                    +880 1625305856
                </a>
            </p>

            <!-- SOCIAL ICONS -->
            <div class="footer-social">
                <a href="https://github.com/jubayer-04" target="_blank" class="social-icon github">
                    <i class="fa-brands fa-github"></i>
                </a>
                <a href="https://www.facebook.com/share/1DXR363sqo/" target="_blank" class="social-icon facebook">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="https://www.linkedin.com/in/jubayer-hossain-628b92292?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app" target="_blank" class="social-icon linkedin">
                    <i class="fa-brands fa-linkedin-in"></i>
                </a>
                <a href="https://x.com/jubayer_11" target="_blank" class="social-icon twitter">
                    <i class="fa-brands fa-x-twitter"></i>
                </a>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="footer-right">
            <p>
                Logged in as<br>
                <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>
            </p>
        </div>

    </div>
</footer>
