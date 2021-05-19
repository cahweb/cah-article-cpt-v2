<?php
if( !class_exists( 'CAH_ArticleMetaField' ) ) {
    class CAH_ArticleMetaField
    {
    // Private members
        private $_name, $_label, $_type, $_span, $_meta;

    // Public functions
        public function __construct( array &$meta, string $name, string $label, string $span = '', string $type = 'text' ) {
            $this->_meta = $meta;
            $this->_name = $name;
            $this->_label = $label;
            $this->_type = $type;
            $this->_span = $span;
        }


        public function __toString() {
            ob_start();
            ?>
            <tr>
                <td>
                    <label><?= $this->_label ?>:</label>
                </td>
                <td>
                    <input type="<?= $this->_type ?>"
                        name="<?= $this->_name ?>"
                        value="<?= isset( $this->_meta[ $this->_name ] ) ? $this->_meta[ $this->_name ] : '' ?>"
                        size="50";
                    >
                    <?= !empty( $this->_span ) ? "<span><em>$this->_span</em></span>" : "" ?>
                </td>
            </tr>
            <?php
            return ob_get_clean();
        }


        // Getters
        public function get_name() { return $this->_name; }
        public function get_label() { return $this->_label; }
        public function get_type() { return $this->_type; }
        public function get_span() { return $this->_span; }
    }
}
?>