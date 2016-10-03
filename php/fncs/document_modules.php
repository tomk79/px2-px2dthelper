<?php
/**
 * px2-px2dthelper
 */
namespace tomk79\pickles2\px2dthelper;

/**
 * document_modules.php
 */
class document_modules{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * px2dthelper main
	 */
	private $main;

	/**
	 * constructor
	 *
	 * @param object $px $pxオブジェクト
	 * @param object $main main.php のインスタンス
	 */
	public function __construct( $px, $main ){
		$this->px = $px;
		$this->main = $main;
	}

	/**
	 * ドキュメントモジュール定義をロードする
	 *
	 * モジュール定義の情報から、スタイルシートとJavaScriptコードを生成します。
	 *
	 * HTMLのheadセクション内に、下記のようにコードを記述します。
	 *
	 * ```
	 * <?php
	 * print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->load();
	 * ?>
	 * ```
	 *
	 * @return string HTMLコード(styleタグ、およびscriptタグ)
	 */
	public function load(){
		$rtn = '';
		$rtn .= '<style type="text/css">'.$this->build_css().'</style>';
		$rtn .= '<script type="text/javascript">'.$this->build_js().'</script>';
		return $rtn;
	}

	/**
	 * ドキュメントモジュール定義のスタイルを統合
	 *
	 * モジュール定義の情報から、スタイルシートを生成します。
	 *
	 * スタイルシートファイル(例: `/common/styles/contents.css` など)に、下記のようにコードを記述します。
	 *
	 * ```
	 * <?php
	 * print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->build_css();
	 * ?>
	 * ```
	 *
	 * @return string CSSコード
	 */
	public function build_css(){
		$conf = $this->main->get_px2dtconfig();
		$array_files = array();
		foreach( $conf->paths_module_template as $key=>$row ){
			$array_files[$key] = array();
			$array_files[$key] = array_merge( $array_files[$key], glob($row."**/**/module.css") );
			$array_files[$key] = array_merge( $array_files[$key], glob($row."**/**/module.css.scss") );
		}
		$rtn = '';
		foreach( $array_files as $packageId=>$array_files_row ){
			foreach( $array_files_row as $path ){
				preg_match( '/\/([a-zA-Z0-9\.\-\_]+?)\/([a-zA-Z0-9\.\-\_]+?)\/[a-zA-Z0-9\.\-\_]+?$/i', $path, $matched );
				$rtn .= '/**'."\n";
				$rtn .= ' * module: '.$packageId.':'.$matched[1].'/'.$matched[2]."\n";
				$rtn .= ' */'."\n";
				$tmp_bin = $this->px->fs()->read_file( $path );
				if( $this->px->fs()->get_extension( $path ) == 'scss' ){
					$tmp_current_dir = realpath('./');
					chdir( dirname( $path ) );
					$scss = new \scssc();
					$tmp_bin = $scss->compile( $tmp_bin );
					chdir( $tmp_current_dir );
				}

				$tmp_bin = $this->build_css_resources( $path, $tmp_bin );
				$rtn .= $tmp_bin;
				$rtn .= "\n"."\n";

				unset($tmp_bin);
			}
		}
		return trim($rtn);
	}

	/**
	 * CSSリソースをビルドする
	 * @param string $path CSSファイルのパス
	 * @param string $bin ビルド前のCSSコード
	 * @return string CSSコード
	 */
	private function build_css_resources( $path, $bin ){
		$rtn = '';
		while( 1 ){
			if( !preg_match( '/^(.*?)url\s*\\((.*?)\\)(.*)$/si', $bin, $matched ) ){
				$rtn .= $bin;
				break;
			}
			$rtn .= $matched[1];
			$rtn .= 'url("';
			$res = trim( $matched[2] );
			if( preg_match( '/^(\"|\')(.*)\1$/si', $res, $matched2 ) ){
				$res = trim( $matched2[2] );
			}
			$res = preg_replace('/#.*$/si', '', $res);
			$res = preg_replace('/\\?.*$/si', '', $res);
			if( is_file( dirname($path).'/'.$res ) ){
				$ext = $this->px->fs()->get_extension( dirname($path).'/'.$res );
				$ext = strtolower( $ext );
				$mime = 'image/png';
				switch( $ext ){
					// styles
					case 'css': $mime = 'text/css'; break;
					// images
					case 'png': $mime = 'image/png'; break;
					case 'gif': $mime = 'image/gif'; break;
					case 'jpg': case 'jpeg': case 'jpe': $mime = 'image/jpeg'; break;
					case 'svg': $mime = 'image/svg+xml'; break;
					// fonts
					case 'eot': $mime = 'application/vnd.ms-fontobject'; break;
					case 'woff': $mime = 'application/x-woff'; break;
					case 'otf': $mime = 'application/x-font-opentype'; break;
					case 'ttf': $mime = 'application/x-font-truetype'; break;
				}
				$res = 'data:'.$mime.';base64,'.base64_encode($this->px->fs()->read_file(dirname($path).'/'.$res));
			}
			$rtn .= $res;
			$rtn .= '")';
			$bin = $matched[3];
		}

		return $rtn;
	}

	/**
	 * ドキュメントモジュール定義のスクリプトを統合
	 *
	 * モジュール定義の情報から、JavaScriptコードを生成します。
	 *
	 * スクリプトファイル(例: `/common/scripts/contents.js` など)に、下記のようにコードを記述します。
	 *
	 * ```
	 * <?php
	 * print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->build_js();
	 * ?>
	 * ```
	 *
	 * @return string JavaScriptコード
	 */
	public function build_js(){
		$conf = $this->main->get_px2dtconfig();
		$array_files = array();
		foreach( $conf->paths_module_template as $row ){
			$array_files = array_merge( $array_files, glob($row."**/**/module.js") );
		}
		$rtn = '';
		foreach( $array_files as $path ){
			$rtn .= $this->px->fs()->read_file( $path );
		}
		return trim($rtn);
	}

}