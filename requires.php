<script type="text/javascript">
/* <![CDATA[ */
(function($){
    var conds = [
        <?php $_comma = ''; foreach($Requires as $rf => $rv) : ?>
        <?php echo $_comma; ?>{
            el : 'boots_metabox_' + '<?php echo $rf; ?>',
            val : '<?php echo $rv; ?>'
        }
        <?php $_comma = ', '; endforeach; ?>
    ];
    $('.boots-form').on('boots-form:init', function (e, form){
        form.conditionalElement(
            $('[data-id="<?php echo $uniqueid; ?>"]'),
            conds
        );
    });
})(jQuery);
/* ]]> */
</script>
