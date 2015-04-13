<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * field.text.php
 */
class field_text extends field_base{

	/**
	 * データをバインドする
	 * @param mixed $fieldData フィールドにバインドするデータ
	 * @param string $mode モード(通常は 'finalize' が渡される)
	 * @return string バインドして生成されたHTMLコード
	 */
	public function bind( $fieldData, $mode = 'finalize' ){
		$rtn = $fieldData;
		$rtn = htmlspecialchars( $rtn );
		$rtn = preg_replace( '/\r\n|\r|\n/', '<br />', $rtn );

		return $rtn;
	}

}
