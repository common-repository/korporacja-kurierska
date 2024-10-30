<?php if ( ! empty( $name ) && ! empty( $content ) ): ?>
    <tr>
        <th><?= $name; ?></th>
        <td><?= $content; ?></td>
    </tr>

    <script type="text/javascript">
        (function ($) {
            $(document).ready(function () {
                var select2Selector = '.add-select2';

                if ($(select2Selector).length > 0) {
                    $(select2Selector).select2();
                }
            });
        })(jQuery);

    </script>
<?php endif; ?>
