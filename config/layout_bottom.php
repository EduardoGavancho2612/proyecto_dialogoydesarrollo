<?php
/**
 * Cierre comun del panel (footer, fin de main-wrapper, scripts).
 * Variables esperadas antes del include:
 *   $extra_scripts (string, opcional) HTML adicional de <script> (plugins especificos de la pagina).
 */
?>
            </div>
        </div>
        <!--**********************************
            Content body end
        ***********************************-->

        <!--**********************************
            Footer start
        ***********************************-->
        <div class="footer">
            <div class="copyright">
                <p>Dialogo y Desarrollo &copy; <?php echo date('Y'); ?></p>
            </div>
        </div>
        <!--**********************************
            Footer end
        ***********************************-->

    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script src="../vendor/global/global.min.js"></script>
    <script src="../js/quixnav-init.js"></script>
    <script src="../js/custom.min.js"></script>
    <?php if (!empty($extra_scripts)) { echo $extra_scripts; } ?>

</body>

</html>
