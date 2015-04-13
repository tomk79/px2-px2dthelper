<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * field.href.php
 */
class field_href extends field_base{

	/**
	 * データをバインドする
	 * @param mixed $fieldData フィールドにバインドするデータ
	 * @param string $mode モード(通常は 'finalize' が渡される)
	 * @return string バインドして生成されたHTMLコード
	 */
	public function bind( $fieldData, $mode = 'finalize' ){
		$rtn = $fieldData;
		$rtn = htmlspecialchars($rtn);
		return $rtn;
	}

}
