<?php
/**
 * Test for pickles2\px2-px2dthelper
 */

class sitemapTest extends PHPUnit\Framework\TestCase{

	/**
	 * setup
	 */
	public function setup() : void{
		set_time_limit(60);
		$this->fs = new \tomk79\filesystem();
		require_once(__DIR__.'/../php/simple_html_dom.php');
	}

	/**
	 * PX=px2dthelper.sitemap.create のテスト
	 */
	public function testSitemapCreat(){

		// ---------------------------
		// 新しいサイトマップファイルを作成
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.create&filename=create_new_sitemap' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.xlsx' ) );


		// ---------------------------
		// 同じファイル名でもう一度作成
		// ただし、ファイル名の一部を大文字に。大文字・小文字は区別しないのが正解。
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.create&filename=create_NEW_sitemap' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertFalse( $json->result ); // 失敗 `false` が得られる
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.xlsx' ) );


	} // testSitemapCreat()

	/**
	 * PX=px2dthelper.sitemap.csv2xlsx のテスト
	 */
	public function testSitemapCsv2Xlsx(){

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.csv2xlsx&filename=create_new_sitemap' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.xlsx' ) );

	} // testSitemapCsv2Xlsx()

	/**
	 * PX=px2dthelper.sitemap.xlsx2csv のテスト
	 */
	public function testSitemapXlsx2Csv(){

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.xlsx2csv&filename=create_new_sitemap' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );
		$this->assertTrue( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.xlsx' ) );

	} // testSitemapXlsx2Csv()

	/**
	 * PX=px2dthelper.sitemap.filelist のテスト
	 */
	public function testSitemapFileList(){

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.filelist' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertIsString( $json->message );
		$this->assertIsObject( $json->list );
		$this->assertIsObject( $json->list_origcase );
		$this->assertIsArray( $json->fullname_list );
		$this->assertIsArray( $json->fullname_list_origcase );

	} // testSitemapFileList()

	/**
	 * PX=px2dthelper.sitemap.download のテスト
	 */
	public function testSitemapFileDownload(){

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.download&filefullname=create_new_sitemap.xlsx' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertIsString( $json->message );
		$this->assertSame( $json->filename, 'create_new_sitemap.xlsx' );
		$this->assertIsString( $json->base64 );
		$this->assertFalse( isset($json->bin) );

	} // testSitemapFileDownload()

	/**
	 * PX=px2dthelper.page.add_page_info_raw のテスト
	 */
	public function testPageAddPageInfoRaw(){

		$page_info = array(
			'page_info' => array(
				'path'=>'/added_page_sample/index.html',
				'title'=>'Page Title',
			),
		);
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.add_page_info_raw&filefullname=create_new_sitemap.csv&row=1&'.http_build_query($page_info) ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );

	} // testPageAddPageInfoRaw()

	/**
	 * PX=px2dthelper.page.get_page_info_raw のテスト
	 */
	public function testPageGetPageInfoRaw(){

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.page.get_page_info_raw&filefullname=create_new_sitemap.csv&row=1' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertIsArray( $json->sitemap_definition );
		$this->assertIsArray( $json->page_info );

	} // testPageGetPageInfoRaw()

	/**
	 * PX=px2dthelper.sitemap.delete のテスト
	 */
	public function testSitemapDelete(){

		// ---------------------------
		// サイトマップファイルを削除
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=px2dthelper.sitemap.delete&filename=create_new_sitemap' ] );
		clearstatcache();
		// var_dump($output);
		$json = json_decode( $output );
		// var_dump($json);
		$this->assertTrue( is_object($json) );
		$this->assertTrue( $json->result );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.csv' ) );
		$this->assertFalse( $this->fs->is_file( __DIR__.'/testData/standard/px-files/sitemaps/create_new_sitemap.xlsx' ) );


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	} // testSitemapDelete()




	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}// passthru()

}
