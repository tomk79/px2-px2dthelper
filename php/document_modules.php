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
			if( is_file( dirname($path).'/'.$res ) ){
				$ext = $this->px->fs()->get_extension( dirname($path).'/'.$res );
				$ext = strtolower( $ext );
				$mime = 'image/png';
				switch( $ext ){
					case 'png': $mime = 'image/png'; break;
					case 'gif': $mime = 'image/gif'; break;
					case 'jpg': case 'jpeg': case 'jpe': $mime = 'image/jpeg'; break;
					case 'svg': $mime = 'image/svg+xml'; break;
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

	/**
	 * スタイルガイドを生成
	 * 
	 * モジュール定義の情報から、スタイルガイドページを生成します。
	 * 
	 * コンテンツに、下記のコードを記述します。
	 * 
	 * ```
	 * <!-- autoindex -->
	 * 
	 * <?php
	 * print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->build_styleguide();
	 * ?>
	 * ```
	 * 
	 * @return string スタイルガイドのHTMLコード
	 */
	public function build_styleguide(){
		$conf = $this->main->get_px2dtconfig();
		$obj_module_templates = $this->main->module_templates();
		$array_files = array();
		foreach( $conf->paths_module_template as $key=>$row ){
			$array_files[$key] = array();
			$array_files[$key] = array_merge( $array_files[$key], glob($row."**/**/template.html") );
		}
		$rtn = '';
		foreach( $array_files as $packageId=>$array_files_row ){
			$package_info = $obj_module_templates->get_package_info( $packageId );
			if( strlen( @$package_info->info->name ) ){
				$rtn .= '<h2>'.htmlspecialchars( $package_info->info->name ).'</h2>'."\n";
			}else{
				$rtn .= '<h2>'.$packageId.'</h2>'."\n";
			}
			if( strlen( @$package_info->readme ) ){
				$rtn .= '<div>'.$package_info->readme.'</div>'."\n";
			}
			foreach( $array_files_row as $path ){
				preg_match( '/\/([a-zA-Z0-9\.\-\_]+?)\/([a-zA-Z0-9\.\-\_]+?)\/[a-zA-Z0-9\.\-\_]+?$/i', $path, $matched );

				$mod = $obj_module_templates->get( $packageId.':'.$matched[1].'/'.$matched[2] );

				$sample_data = null;
				if( is_array( @$mod->get_info()->sample_data ) && count( @$mod->get_info()->sample_data ) ){
					$sample_data = @$mod->get_info()->sample_data;
				}

				$tmp_bin = $mod->get_template();
				$rtn .= '<h3>'.$mod->get_name().'</h3>'."\n";
				if( strlen( $mod->get_readme() ) ){
					$rtn .= $mod->get_readme();
				}

				$rtn .= $this->build_styleguide_mk_code_view($tmp_bin, array('title'=>'module template code'));
				$rtn .= "\n"."\n";

				if( is_array( $sample_data ) ){
					foreach( $sample_data as $rowData ){
						$tmp_sample_html = $obj_module_templates->bind( $packageId.':'.$matched[1].'/'.$matched[2], $rowData->data );
						$tmp_title = @$rowData->title;
						if( !strlen( $tmp_title ) ){ $tmp_title = 'Coding sample'; }
						$rtn .= '<div style="padding:0.5em; background:#ddd; color:#666; font-size:xx-small; margin:1em 0; border:0; border-top:1px solid #999; border-bottom:1px solid #999;">sample: '.htmlspecialchars($tmp_title).'</div>'."\n";
						$rtn .= "\n"."\n";
						$rtn .= '<script>document.write('.json_encode($tmp_sample_html).');</script>';
						$rtn .= "\n"."\n";
						$rtn .= $this->build_styleguide_mk_code_view($tmp_sample_html, array('title'=>'sample code of "'.$tmp_title.'"'));
						$rtn .= "\n"."\n";
					}
				}

			}
		}
		return trim($rtn);
	}

	/**
	 * プレビュー表示HTMLコードを生成する
	 * 
	 * @param string $code プレビューしたいコード
	 * @param array $opt オプション(省略可)
	 * @return string プレビュー表示HTMLコード
	 */
	private function build_styleguide_mk_code_view($code, $opt = array()){
		$rtn = '';
		$rtn .= '<div style="margin:2em 1em 3em; border:1px solid #ddd; padding: 0; background:#f9f9f9; color:#999; border-radius:3px;">';
		$rtn .= '<div style="font-size:xx-small; padding:0.3em 1em;">CODE: '.@htmlspecialchars($opt['title']).'</div>';
		$rtn .= '<pre style="margin:0; border:none; padding: 0; background:transparent; color:inherit;">';
		$rtn .= '<code style="display:block; background:transparent; border:none;">';
		$rtn .= '<textarea style="height:12em; width:100%; border:none; background:transparent; padding: 1em; border-radius:0.5em; color:#000; font-size:small;" readonly="readonly">';
		$rtn .= htmlspecialchars($code);
		$rtn .= '</textarea>';
		$rtn .= '</code>';
		$rtn .= '</pre>';
		$rtn .= '</div>';
		return $rtn;
	}

	/**
	 * コンテンツソースを生成する
	 * 
	 * Pickles 2 Desktop Tool が生成する `data.json` から、カレントコンテンツのHTMLコードを生成します。
	 * 
	 * コンテンツに、下記のコードを記述します。
	 * 
	 * ```
	 * <?php
	 * print (new \tomk79\pickles2\px2dthelper\main($px))->document_modules()->build_content();
	 * ?>
	 * ```
	 * 
	 * @return string 生成されたコンテンツのHTMLコード
	 */
	public function build_content(){
		$obj_module_templates = $this->main->module_templates();

		$c_page_info = $this->px->site()->get_current_page_info();
		$realpath_cont = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$this->px->get_path_controot().'/'.$this->px->get_path_content() );
		$realpath_files = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$this->px->path_files() );
		$realpath_json = $this->px->fs()->get_realpath( $realpath_files.'guieditor.ignore/data.json' );
		if( !is_file( $realpath_json ) ){
			$this->px->error( 'datafile is NOT defined. -> '.$realpath_files.'guieditor.ignore/data.json' );
			return '';
		}
		$json = json_decode( $this->px->fs()->read_file( $realpath_json ) );

		$rtn = '';

		foreach( $json->bowl as $bowl_name=>$data ){
			if( $bowl_name == 'main' ){
				$rtn .= $obj_module_templates->bind( $data->modId, $data );
			}else{
				$rtn .= '<'.'?php ob_start(); ?'.'>'."\n";
				$rtn .= $obj_module_templates->bind( $data->modId, $data );
				$rtn .= '<'.'?php $px->bowl()->send( ob_get_clean(), '.json_encode($bowl_name).' ); ?'.'>'."\n";
			}
		}

		$path_cache = $this->px->realpath_files_private_cache('/__contents.ignore.html');
		$this->px->fs()->save_file( $path_cache, $rtn );
		ob_start();
		include( $path_cache );
		$rtn = ob_get_clean();

		return $rtn;
	}

}
