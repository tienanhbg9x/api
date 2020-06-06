<?php
namespace App\helpers;
use \DOMDocument;

class html_cleanup{
	
	//Các tag cho phép
	var $valid_elements	= array ("a", "b", "blockquote", "br", "center", "del", "div", "em", "font", "h2", "h3", "h4", "i", "img", "ins", "li", "hr", "ol",
											"p", "pre", "s", "span", "strong", "strike", "sub", "sup", "table", "tbody", "td", "th", "tr", "u", "ul");
	
	//Phần mở rộng cho các tag đc phép, các attribute cho phép trong tag
	var $extended_valid_elements = array("a"				=> array("href", "name", "rel", "style", "target", "title"),
													 "b"				=> array("style"),
													 "blockquote"	=> array("style", "title"),
													 "br"				=> array("clear", "title"),
													 "center"		=> array(),
													 "del"			=> array("style", "title"),
													 "div"			=> array("align", "style", "title", "class"),
													 "em"				=> array("style", "title"),
													 "font"			=> array("color", "face", "title"),
													 "h2"				=> array(),
													 "h3"				=> array(),
													 "h4"				=> array(),
													 "i"				=> array("style", "title"),
													 "img"			=> array("align", "alt", "border", "height", "hspace", "idata", "src", "style", "title", "vspace", "width"),
													 "ins"			=> array("style", "title"),
													 "li"				=> array("style", "title", "type"),
													 "hr"				=> array("align", "noshade", "size", "style", "title"),
													 "ol"				=> array("style", "title", "type"),
													 "p"				=> array("align", "style", "title"),
													 "pre"			=> array("style", "title"),
													 "s"				=> array("style", "title"),
													 //"span"			=> array("style", "title", "class"),
                                        "span"        => array(),
													 "strong"		=> array("style"),
													 "strike"		=> array("style", "title"),
													 "sub"			=> array("style", "title"),
													 "sup"			=> array("style", "title"),
													 "table"			=> array("align", "bgcolor", "border", "bordercolor", "cellpadding", "cellspacing", "height", "style", "title", "width"),
													 "tbody"			=> array(),
													 "td"				=> array("align", "bgcolor", "colspan", "height", "nowrap", "rowspan", "style", "title", "valign", "width"),
													 "th"				=> array("align", "bgcolor", "colspan", "height", "nowrap", "rowspan", "style", "title", "valign", "width"),
													 "tr"				=> array("align", "bgcolor", "height", "nowrap", "style", "title", "valign"),
													 "u"				=> array("style", "title"),
													 "ul"				=> array("style", "title", "type"),
													 );
	var $arrayTagCheckNull = array('p','div','pan','font','strong','a','li','b','select','detail','em','h1','h2','h3','h4','h5');
	//Các style không được phép dùng
	var $invalid_styles = array("behavior", "background-image", "background", "list-style-image", "expression", "/*", "*/","position","white-space");
	
	//Các style được phép dùng để override invalid_styles (ví dụ background bị xóa thì vẫn phải cho background-color)
	var $override_styles = array("background" => array("background-color"),);
	
	//Các giao thức được dùng
	var $web_protocol = array("http://", "https://", "ftp://", "mailto:");

	var $input_html;
	var $output_html;
	protected $download_img = false; //mặc định không cấu hình bóc tách image ra để tải về
	protected $arrayImages			= array();
	protected $arrayImgBase64		= array();
	protected $alt_image				= "";
	protected $alt_img_stt			= 0;
	protected $ignore_check_protoco = false;
	var $arrayIgnoreDomainImg = array();
	var $DOMDoc;
	//Lưu lại log
	var $log_string = "";
	protected $arrayImage = array();
	protected $breakImage = false;
	
	/**
	Khởi tạo hàm
	*/
	function __construct($input_html){
		$input_html = $this->removeScript($input_html);
		//Do something here
		$this->input_html = $input_html;
	}

	function removeScript($string){
		$string = preg_replace ('/<script.*?\>.*?<\/script>/si', ' ', $string);
		$string = preg_replace ('/<style.*?\>.*?<\/style>/si', ' ', $string);
		return $string;
	}
	
	/**
	Bắt đầu làm sạch chuỗi HTML
	*/
	function clean(){
		//nếu yêu cầu tải ảnh lên server riêng thì nhận dạng ảnh base64 để tách ra
		if($this->download_img){
			//lấy ra những định dạng ảnh base64
			preg_match_all('/data:image([^"]*)"/ui', $this->input_html, $matches);
			if(isset($matches[1])){
				unset($matches[0]);
				foreach($matches[1] as $data_img){
					$data_img 											= "data:image" . $data_img;
					//echo $data_img . '<hr>';
					$src_img 											= "http://" . md5($data_img);
					$this->input_html 										= str_replace($data_img,$src_img,$this->input_html);
					$this->arrayImages[md5($src_img)] 			= $src_img;
					$this->arrayImgBase64[md5($src_img)]		= $data_img;
				}	
			}
		}//end if
		
		//Sử dụng strip_tags để làm sạch HTML
		$this->html_strip_tags();
		
		//Sử dụng DOMDocument để làm sạch
		$this->DOMDocument_cleanup();

		//Sau khi đã trải qua công đoạn làm sạch gán outout = input
		$this->output_html = $this->input_html;
		
		//Cleanup HTML Comment
		$this->output_html = preg_replace('/&lt;!--(.|\s)*?--&gt;/', '&nbsp;', $this->output_html);
		
		//Cleanup censored words
		global $array_censored_words;
		global $con_censored_replace_string;
		if(isset($array_censored_words) && isset($con_censored_replace_string)){
			$this->output_html = str_ireplace($array_censored_words, $con_censored_replace_string, $this->output_html);
		}
		
		//Convert ký tự NCR -> UTF-8
		$convmap = array(0x0, 0x2FFFF, 0, 0xFFFF);
		$this->output_html = @mb_decode_numericentity($this->output_html, $convmap, "UTF-8");
		
	}

	function setImgToNoImg(){
		$this->breakImage = true;
	}

	function removeAttribute($arrayTagAttr = array()){
		foreach($arrayTagAttr as $value){
			$value = explode(".",$value);
			if(count($value) < 2) continue;
			$tag = trim($value[0]);
			$attr = trim($value[1]);
			if(isset($this->extended_valid_elements[$tag][$attr])){
				unset($this->extended_valid_elements[$tag][$attr]);
			}
		}
	}
	
	/**
	Sử dụng strip_tags để remove các thẻ ko đc phép
	*/
	function html_strip_tags(){
		
		$tag_allow = "";
		reset($this->valid_elements);
		//Tạo các tag_allow
		foreach ($this->valid_elements as $m_key => $m_value) $tag_allow .= "<" . $m_value . ">";

		//Loại các thẻ ko cho phép
		$this->input_html = strip_tags($this->input_html, $tag_allow);
	}
	
	/**
	Làm sạch HTML bằng DOMDocument
	*/
	function DOMDocument_cleanup(){
		//Khởi tạo 1 DOM Document mới
		$this->DOMDoc = new DOMDocument("1.0", "UTF-8");
		
		//Cho thẻ HTML, meta UTF8, <body> vào DOM để tránh lỗi khi loadHTML
		$this->input_html = 	'<html>' . 
										'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . 
										'<body>' .
											$this->input_html . 
										'</body>' . 
								  	'</html>';

		//Load input HTML vào DOM Document, dùng @ để tránh lỗi
		@$this->DOMDoc->loadHTML($this->input_html);
		 $this->DOMDoc->preserveWhiteSpace = false;
		 $this->DOMDoc->validateOnParse = true;
		
		//Loại bỏ các tag không cho phép
		$this->DOMDocument_cleanup_tag();

		//Loại các thẻ tr, td, th đứng 1 mình ko có cha
		$this->DOMDocument_cleanup_missing_parent_tr_td();
		
		//Loại các attribute không được phép
		$this->DOMDocument_cleanup_attribute();

		//Trả lại input chuỗi HTML đã validate xong		
		$this->input_html = $this->DOMDoc->saveHTML();

		// Replace các ký tự FCK sang UTF-8
		$this->input_html	= replaceFCK($this->input_html, 1);

		
		
		//Tìm đến đầu body và /body để cắt chuỗi
		$start_pos 	= strpos($this->input_html,"<body>");
		$end_pos 	= strpos($this->input_html,"</body>");
		
		// Không tìm thấy vị trí thẻ body thì trả về chuỗi rỗng
		if($start_pos === false) $this->input_html	= "";
		else $this->input_html = substr($this->input_html, $start_pos + 6, $end_pos - $start_pos - 6);
	}
	
	
	/**
	 * Hàm để bóc ảnh ra khỏi content
	 */
 	function DOMDocument_clean_image($arrayIgnoreDomain = array(), $alt_image = ""){
 		$this->download_img = true;
 		$this->arrayIgnoreDomainImg = $arrayIgnoreDomain;
 		$this->alt_image		= $alt_image;
 	}
 	
 	/**
 	 * hàm gọi ra danh sách ảnh được sàn lọc
 	 */
 	function getListImages(){
 		foreach($this->arrayImgBase64 as $key => $val){
 			$this->arrayImages[$key] = $val;
 		}
 		$arrayReturn = $this->arrayImages;
 		return $arrayReturn;
 	}
 	
 	function setIgnoreCheckProtocol(){
 		$this->ignore_check_protoco = true;
 	}
 	
 	/**
 	 * Hàm bắn ảnh sang server images
 	 */
 	 function uploadImage($maxwidth = 1400, $maxheight = 2500,$arrayOtherImage = array(),$downloadRealtime = true,$cla_id = 0){
		$arrayImage = (array) $this->getListImages();
		 if(is_array($arrayOtherImage)){
			 foreach($arrayOtherImage as $key => $value){
				 $arrayImage[md5($value)] = $value;
			 }
		 }
		//print_r($arrayImage);
		if(empty($arrayImage)) return '';
		//chưa gọi class curl thì gọi
		$arrayPost = array();
		foreach($arrayImage as $key => $val){
		     if(strpos($val,".gif") !== false) continue;
			 $arrayPost[$key]["url"] = $val;
			 $arrayPost[$key]["name"] = $this->alt_image;
		}
		$data = downloadPicure($arrayPost,$downloadRealtime,$cla_id);
		$data = (array) json_decode($data,true);
		 $arrayReturn = array();
		//*
		foreach($arrayImage as $key => $url){
			if(isset($data[$key])){
				$this->output_html = str_replace($key,$data[$key]["url"],$this->output_html);
			}else{
				$this->output_html = str_replace($key,$url,$this->output_html);
			}
		}

		 foreach($data as $key => $val){
			 $val["filename"] = isset($val["filename"]) ? $val["filename"] : $val["name"];
			 $arrayReturn[] = array("name" => $val["filename"],"width" => @$val["width"],"height" => @$val["height"]);
		 }
		//*/
		return $arrayReturn;
 	 }
	
	
	/**
	Loại bỏ các tag không cho phép
	*/
	function DOMDocument_cleanup_tag(){

		$this->log_string .= "---START REMOVE TAG ---\n";
		
		//Lọc bỏ tag không được phép
		//gắn node với các tất cả các tag dưới dạng tham chiếu
		$node = $this->DOMDoc->getElementsByTagName("*");
		
		//Khai báo mảng những node cần delete
		$delete_node = array();
		$new_valid_elements = array_merge($this->valid_elements, array("html", "body"));
		
		foreach ($node as $mynode){
			//echo $mynode->nodeName . "\n";
			$this->log_string .= $mynode->nodeName . " ";
			if (array_search($mynode->nodeName, $new_valid_elements) === false){
				$this->log_string .= "delete";
				//gán vào delete node
				$delete_node[] = $mynode;
			}
			$this->log_string .= "\n";
			//xoa cac the trong
			if(trim($mynode->nodeValue) == '' && !$mynode->hasChildNodes()){
				if(in_array($mynode->nodeName,$this->arrayTagCheckNull)){
					$delete_node[] = $mynode;
				}
			}
		}
		//print_r($delete_node);
		//Loop delete node để xóa
		foreach ($delete_node as $mynode){
			//Tự xóa nó bằng cách nhẩy đến nút cha rồi xóa
			if(is_object($mynode->parentNode)) $mynode->parentNode->removeChild($mynode);
		}
			
	}
	
	/**
	Loại bỏ các atttribute không được phép dùng
	*/
	function DOMDocument_cleanup_attribute(){
		$this->log_string .= "---START REMOVE ATTRIBUTE ---\n";
		//Loop lần 2 để lọc bỏ các Attribute không đc phép
		$node = $this->DOMDoc->getElementsByTagName("*");
		/*
		foreach ($node as $mynode) {
			//Nếu nodeName có trong array
			if (isset($this->extended_valid_elements[$mynode->nodeName])) {
				$finish = true;
				while($finish){
					$finish = false;
					foreach ($mynode->attributes as $attribute_name => $attribute_value) {
						if($mynode->nodeName == "img") echo $attribute_name . "\n";
						if (!in_array($attribute_name, $this->extended_valid_elements[$mynode->nodeName])) {
							$finish = true;
							$mynode->removeAttribute($attribute_name);
						}
					}
				}
			}
		}
		//*/
		//Loop node
		foreach ($node as $mynode){

			//Nếu nodeName có trong array
			if (isset($this->extended_valid_elements[$mynode->nodeName])){


				// Tạo 1 array remove
				$remove_attr_array = array();

				//Loop toàn bộ attribute
				foreach ($mynode->attributes as $attribute_name => $attribute_value){

					$this->log_string .= $mynode->nodeName . " > " . $attribute_name;

					//Nếu atttribute không có trong định nghĩa thì remove luôn attribute
					if (array_search($attribute_name, $this->extended_valid_elements[$mynode->nodeName]) === false){
						//$mynode->removeAttribute($attribute_name);
						// Chưa remove vội mà gán vào 1 array các attr_name vì remove sẽ bị break vòng for
						$remove_attr_array[] = $attribute_name;

						$this->log_string .= " REMOVE";
					}

					//Đối với attribute có trong định nghĩa thì check style, src, href chống nhét Javascript
					else{
						switch ($attribute_name){
							//Check attribute style
							case "style":
								//bẻ dấu ;
								$new_style_str = "";
								$style_array = explode(";", $attribute_value->value);

								//Loop cac value style
								foreach ($style_array as $m_key => $m_value){
									reset($this->invalid_styles);

									//Gán biến found_invalid_style bằng false, mặc định luôn không tìm thấy
									$found_invalid_style = false;

									foreach ($this->invalid_styles as $ivs_key => $ivs_value){
										//Nếu tìm đc invalid style thì gán $found_invalid_style = true;
										if (stripos($m_value, $ivs_value) !== false){

											//Gán luôn found_invalid_style = true để sau remove
											$found_invalid_style = true;

											//Nếu tồn tại trong override thì check tiếp
											if (isset($this->override_styles[$ivs_value])){
												reset($this->override_styles[$ivs_value]);
												//Loop các giá trị override
												foreach ($this->override_styles[$ivs_value] as $ovs_key => $ovs_value){
													//Nếu tìm thấy override ở đầu tiên thì
													if (stripos($m_value, $ovs_value) !== false && stripos($m_value, $ovs_value) == 0){
														//Gán lại $found_invalid_style = false và tiếp tục chạy các rule tiếp theo
														$found_invalid_style = false;
														break;
													}
												}
											}
											//Nếu ko có trong override thì gán đây là invalid style và thóat vòng lặp
											else{
												//Thoát vòng for
												break;
											}
										}
									}

									//Nếu không tìm thấy invalid style thì gán thêm vào $new_style_str
									if (!$found_invalid_style){
										//Nếu value khác rỗng
										if (trim($m_value) != "") $new_style_str .= trim($m_value) . ";";
									}

								}
								//Gán lại attribute style
								$mynode->setAttribute($attribute_name, $new_style_str);
							break;
							//Kết thúc check attribute style

							//Check attribute src, href
							case "src":
							case "href":
								//echo $attribute_value->value . '<hr>';
								//Kiểm tra giao thức của src, href
								reset($this->web_protocol);

								//Gán biến $trust_protocol luôn là false
								$found_trust_protocol = false;

								foreach($this->web_protocol as $m_key => $m_value){
									//Nếu vị trí đầu tiên đúng với các giao thức định nghĩa thì gán $found_trust_protocol = true
									if (stripos($attribute_value->value, $m_value) !== false && stripos($attribute_value->value, $m_value) == 0){
										$found_trust_protocol = true;
										break;
									}
								}

								//Nếu giao thức không có trong định nghĩa, mặc định gán lại là http:// tránh XSS đa phần trường hợp này sẽ ko show đúng
								if (!$found_trust_protocol && !$this->ignore_check_protoco) $mynode->setAttribute($attribute_name, "http://" . $attribute_value->value);

								//replace &amp; -> & trong src va href
								//else $mynode->setAttribute($attribute_name, str_replace("&amp;", "&", $attribute_value->value));

								//Thẻ a thì thêm target và rel vào
								if ($mynode->nodeName == "a"){
									$mynode->setAttribute("target", "_blank");
									// Check chỉ lấy 3 link đầu tiên của Vật giá bỏ nofollow, còn các link khác thì để nofollow
									$nofollow	= true;
									// Nếu link bị nofolow thì gán
									if($nofollow) $mynode->setAttribute("rel", "nofollow");
									// Ngược lại thì remove attr rel
									else $remove_attr_array[] = "rel";
								}

								//echo $attribute_value->value;

							break;
							//Kết thúc check attribute src, href
						}
					}

					$this->log_string .= "\n";
				}//end foreach attr

				// Sau khi foreach remove all attribute ko được phép
				foreach ($remove_attr_array as $key => $value) $mynode->removeAttribute($value);
				
				//nếu yêu cầu bóc tách ảnh để download về thì bắt đầu lọc ra
				if ($this->download_img && ($mynode->nodeName == "img")){
					
					$src 				= $mynode->getAttribute("src");
					$src_md5 		= md5($src);
					//nếu trong những domain bỏ qua thì bỏ qua không download ảnh về nữa
					$chekc_replace = true;
					foreach($this->arrayIgnoreDomainImg as $domainIgnore){
						if(empty($src) || empty($domainIgnore)) continue;
						if(strpos(strtolower($src),$domainIgnore) !== false){
							$chekc_replace = false;
							break;
						}
					}
					//Kiểm tra giao thức của src, href
					reset($this->web_protocol);
					
					//Gán biến $trust_protocol luôn là false
					$found_trust_protocol = false;
					
					foreach($this->web_protocol as $m_key => $m_value){
						//Nếu vị trí đầu tiên đúng với các giao thức định nghĩa thì gán $found_trust_protocol = true
						if (stripos($src, $m_value) !== false && stripos($src, $m_value) == 0){ 
							$found_trust_protocol = true;
							break;
						}
					}
					if(!$found_trust_protocol) $chekc_replace = false;
					if($chekc_replace){
						$this->arrayImages[md5($src)] = $src;
						if($this->alt_image != ""){
							$this->alt_img_stt++;
							if($mynode->getAttribute("alt") == "") $mynode->setAttribute("alt", $this->alt_image . " (Ảnh " . $this->alt_img_stt . ")");
						}
						$mynode->setAttribute("src", $src_md5);
						//nếu convert tu img sang noimg
						if($this->breakImage === true){
							//Lấy src
							$url = $src_md5;
							//Lấy alt text
							$alt_text = trim($mynode->getAttribute("alt"));
							if($alt_text == "") $alt_text = trim($mynode->getAttribute("title"));
							$width	 = intval($mynode->getAttribute("width"));
							$height	 = intval($mynode->getAttribute("height"));
							$this->arrayImage[md5($url)] = array("alt" => $alt_text,"src" => $url,"width" => $width, "height" => $height);

							$newelement = $this->DOMDoc->createElement('noimg');
						   //$link->setAttribute('class', 'player');
						   $newelement->setAttribute('id', $url);
						   $mynode->parentNode->replaceChild($newelement, $mynode);
						}
					}
				}//end if
				
			}
			
		}//End Loop node		
	}
	//End DOMDocument_cleanup_attribute method
	
	/**
	Xóa các thẻ tr, td bị mất thẻ cha (tbody, table)
	*/
	function DOMDocument_cleanup_missing_parent_tr_td(){
	
		$this->log_string .= "---START REMOVE MISSING PARENT TR, TD, TH, TAG ---\n";
		
		$tag_check = array("tbody"	=> "[table]", 
								 "tr"		=> "[tbody][table]", 
								 "td"		=> "[tr]", 
								 "th"		=> "[tr]");
		
		foreach ($tag_check as $m_key => $m_value){
			
			//Loop lần lượt các tag cần check
			$node = $this->DOMDoc->getElementsByTagName($m_key);
			
			//Khai báo mảng những node cần delete
			$delete_node = array();
			
			foreach ($node as $mynode){
				//Kiểm tra node cha của node này có trong định nghĩa ko?
				//Nếu node cha không có trong định nghĩa thì xóa tag vì đây là invalid tag
				if (strpos($m_value, "[" . $mynode->parentNode->nodeName . "]") === false){
					//gán vào delete node
					$delete_node[] = $mynode;
				}
			}
			
			//Loop delete node để xóa
			foreach ($delete_node as $mynode){
				//Tự xóa nó bằng cách nhẩy đến nút cha rồi xóa
				$mynode->parentNode->removeChild($mynode);
			}	
		}	// End foreach tag_check array
	}
	/* Kết thúc DOMDocument_cleanup_missing_parent_tr_td*/
	
	/**
	generate tinyMCE rule 
	Tạo 1 chuỗi string về luật cho tinyMCE
	*/
	function generate_tinyMCE_rule(){
		$tiny_mce_rule_string = "";
		$tiny_mce_rule_string .= 'valid_elements : "' . implode($this->valid_elements, ",") . '",' . "\n";
		
		$tiny_mce_rule_string .= 'extended_valid_elements : "'; 
		reset($this->extended_valid_elements);
		foreach ($this->extended_valid_elements as $m_key => $m_value){
			$tiny_mce_rule_string .= $m_key . "[";
			$tiny_mce_rule_string .= implode($m_value, "|") . '],';
		}
		$tiny_mce_rule_string .= '",' . "\n";
		
		$tiny_mce_rule_string .= 'invalid_styles : "' . implode($this->invalid_styles, ",") . '",' . "\n";; 
		
		return $tiny_mce_rule_string;
	}
	
	/**
	Get gallery image, lấy toàn bộ thẻ image có khả năng nằm trong gallery trả về 1 array
	*/
	function getGalleryImage(){
		
		//Loop toàn bộ thẻ image
		$node = $this->DOMDoc->getElementsByTagName("img");
		$img_array = array();

		//Lấy server gallery
		global $galleryVatgiaServer;
		//Nếu ko có hoặc bằng rỗng thì gán bằng http://localhost:900
		if (!isset($galleryVatgiaServer)) $gallery_host = "http://localhost:9000";
		else $gallery_host = $galleryVatgiaServer;
		if ($gallery_host == "") $gallery_host = "http://localhost:9000";
		//-----------------
		
		//echo $gallery_host . "/gallery_img";
		
		foreach ($node as $mynode){
			//Lấy src
			$url = $mynode->getAttribute("src");
			//Lấy alt text
			$alt_text = $mynode->getAttribute("alt");
			
			//Nếu url thuôc gallery thì bắt đầu bóc tách		
			if (strpos($url, $gallery_host . "/gallery_img") === 0){
				
				$url_array = explode("/", $url);
				if (count($url_array) == 6){
					//gán vào delete node
					$img_array[md5($url)] = array("small_src"		=> $url_array[4] . "/small_" . $url_array[5],
															"alt_text"		=> $alt_text,
															"full_src"		=> $url);
				}
			}
		}
		
		return $img_array;
		
	}
	
	/**
	Get all gallery_img_temp
	*/
	function getAllTempImage($user_id){
		
		global $get_gallery_picture;
		global $con_number_table_user_gallery;
		global $con_max_upload_multi_file;

		//Lấy server gallery
		global $galleryVatgiaServer;
		global $postVatgiaServer;
		
		//Nếu ko có hoặc bằng rỗng thì gán bằng http://localhost:9000
		if (!isset($galleryVatgiaServer)) $gallery_host = "http://localhost:9000";
		else $gallery_host = $galleryVatgiaServer;
		if ($gallery_host == "") $gallery_host = "http://localhost:9000";
		//-----------------
		
		// thêm trường hợp nữa đề phòng user upload từ slave.vatgia.com (Khi post rao vặt)
		if (!isset($postVatgiaServer)) $gallery_host_2 = "http://localhost:9000";
		else $gallery_host_2 = $postVatgiaServer;
		if ($gallery_host_2 == "") $gallery_host_2 = "http://localhost:9000";
		//-----------------
		
		$gallery_path	= $gallery_host . "/gallery_img/" . ($user_id % $con_number_table_user_gallery) . "/";
		
		//Loop toàn bộ thẻ image
		$node = $this->DOMDoc->getElementsByTagName("img");
		
		foreach($node as $mynode){
			
			// Lấy src
			$url = $mynode->getAttribute("src");
			// Lấy alt text
			$alt_text = $mynode->getAttribute("alt");
			
			// Nếu url thuôc gallery thì bắt đầu bóc tách
			if(strpos($url, $gallery_host . "/gallery_img_temp/") === 0 || strpos($url, $gallery_host_2 . "/gallery_img_temp/") === 0){
				
				$url_array	= explode("/", $url);
				if(count($url_array) == 5){
					
					// Lấy $filename
					$filename	= str_replace(array("small_", "medium_"), "", $url_array[4]);
					
					if(!isset($get_gallery_picture->arrGalleryInsert[$filename])){
					
						$id		= $get_gallery_picture->move_gallery_temp($filename, $alt_text);
						if($id > 0){
							$get_gallery_picture->stt++;
							$_POST["image_comment_" . $get_gallery_picture->stt]	= $alt_text;
							$_POST["myimgID_" . $get_gallery_picture->stt]			= $id;
						}
						
					}// End if(!isset($get_gallery_picture->arrGalleryInsert[$filename]))
					else{
						$id = $get_gallery_picture->arrGalleryInsert[$filename];
					}
					
					if($id > 0){
						$mynode->setAttribute("src", $gallery_path . $url_array[4]);
						$mynode->setAttribute("idata", $id);
					}
					
				}// End if(count($url_array) == 5)
				
			}// End if(strpos($url, $gallery_host . "/gallery_img_temp/") === 0)
			elseif(strpos($url, $gallery_path) === 0){
				$url_array	= explode("/", $url);
				if(count($url_array) == 6){
					$id = $mynode->getAttribute("idata");
					// Nếu ko tồn tại method POST thì gán method để save vào table exclusive picture
					if(!isset($get_gallery_picture->arrGallery[$id])){
						$get_gallery_picture->stt++;
						$_POST["image_comment_" . $get_gallery_picture->stt]	= $alt_text;
						$_POST["myimgID_" . $get_gallery_picture->stt]			= $id;
					}
				}
			}
			
			if($get_gallery_picture->stt >= $con_max_upload_multi_file) break;
			
		}// End foreach($node as $mynode)
		
		$this->input_html = $this->DOMDoc->saveHTML();
		$this->clean();
		
	}
	
}
?>