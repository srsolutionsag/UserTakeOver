<?php
namespace srag\plugins\UserTakeOver;

require_once('./Services/Form/classes/class.ilMultiSelectInputGUI.php');
require_once('./Services/User/classes/class.ilObjUser.php');
require_once('./Services/UICore/classes/class.ilTemplate.php');


/**
 * Class ilMultiSelectSearchInput2GUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilusrtoMultiSelectSearchInput2GUI extends \ilMultiSelectInputGUI {

	/**
	 * @var string
	 */
	protected $width;
	/**
	 * @var string
	 */
	protected $height;
	/**
	 * @var string
	 */
	protected $css_class;
	/**
	 * @var int
	 */
	protected $minimum_input_length = 0;
	/**
	 * @var string
	 */
	protected $ajax_link;
	/**
	 * @var \ilTemplate
	 */
	protected $input_template;


	/**
	 * @param string $title
	 * @param string $post_var
	 */
	public function __construct($title, $post_var) {
		global $tpl, $ilUser, $lng;
		if (substr($post_var, - 2) != '[]') {
			$post_var = $post_var . '[]';
		}
		parent::__construct($title, $post_var);

		$this->lng = $lng;
		$this->pl = \ilUserTakeOverPlugin::getInstance();
		$tpl->addJavaScript('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/lib/select2/select2.min.js');
		$tpl->addJavaScript('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/lib/select2/select2_locale_'
		                    . $ilUser->getCurrentLanguage() . '.js');
		$tpl->addCss('././Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/UserTakeOver/lib/select2/select2.css');
		$this->setInputTemplate($this->pl->getTemplate('tpl.multiple_select.html'));
		$this->setWidth('300px');
	}


	/**
	 * @return bool
	 */
	public function checkInput() {
		global $lng;

		if ($this->getRequired() && count($this->getValue()) == 0) {
			$this->setAlert($lng->txt('msg_input_is_required'));

			return false;
		}

		return true;
	}


	/**
	 * @return array
	 */
	public function getValue() {
		$val = parent::getValue();
		if (is_array($val)) {
			return $val;
		} elseif (!$val) {
			return array();
		} else {
			return explode(',', $val);
		}
	}


	/**
	 * @return array
	 */
	public function getSubItems() {
		return array();
	}


	public function getContainerType() {
		return 'crs';
	}


	/**
	 * @return string
	 */
	public function render() {
		$tpl = $this->getInputTemplate();
		$values = $this->getValueAsJson();
		$options = $this->getOptions();

		$tpl->setVariable('POST_VAR', $this->getPostVar());
		$tpl->setVariable('ID', $this->stripLastStringOccurrence($this->getPostVar(), "[]"));
		$tpl->setVariable('ESCAPED_ID', $this->escapePostVar($this->getPostVar()));
		$tpl->setVariable('WIDTH', $this->getWidth());
		$tpl->setVariable('PRELOAD', $values);
		$tpl->setVariable('HEIGHT', $this->getHeight());
		$tpl->setVariable('PLACEHOLDER', $this->pl->txt($this->getContainerType() . '_placeholder'));
		$tpl->setVariable('MINIMUM_INPUT_LENGTH', $this->getMinimumInputLength());
		$tpl->setVariable('CONTAINER_TYPE', $this->getContainerType());
		$tpl->setVariable('Class', $this->getCssClass());

		if (isset($this->ajax_link)) {
			$tpl->setVariable('AJAX_LINK', $this->getAjaxLink());
		}

		if ($this->getDisabled()) {
			$tpl->setVariable('ALL_DISABLED', 'disabled=\'disabled\'');
		}

		if ($options) {
			foreach ($options as $option_value => $option_text) {
				$tpl->setCurrentBlock('item');
				if ($this->getDisabled()) {
					$tpl->setVariable('DISABLED', ' disabled=\'disabled\'');
				}
				if (in_array($option_value, $values)) {
					$tpl->setVariable('SELECTED', 'selected');
				}

				$tpl->setVariable('VAL', \ilUtil::prepareFormOutput($option_value));
				$tpl->setVariable('TEXT', $option_text);
				$tpl->parseCurrentBlock();
			}
		}

		return $tpl->get();
	}

	/**
	 * @return string
	 */
	protected function getValueAsJson() {
		global $ilDB;

		$query = "SELECT firstname, lastname, login, usr_id FROM usr_data WHERE ".$ilDB->in("usr_id", $this->getValue(), false, "integer");
		$res = $ilDB->query($query);
		while ($user = $ilDB->fetchAssoc($res)) {
			$result[] = [
				"id" => $user['usr_id'],
				"text" => $user['firstname']." ".$user['lastname']." (".$user['login'].")"
			];
		}

		return json_encode($result);
	}

	/**
	 * @deprecated setting inline style items from the controller is bad practice. please use the setClass together with an appropriate css class.
	 *
	 * @param string $height
	 */
	public function setHeight($height) {
		$this->height = $height;
	}


	/**
	 * @return string
	 */
	public function getHeight() {
		return $this->height;
	}


	/**
	 * @deprecated setting inline style items from the controller is bad practice. please use the setClass together with an appropriate css class.
	 *
	 * @param string $width
	 */
	public function setWidth($width) {
		$this->width = $width;
	}


	/**
	 * @return string
	 */
	public function getWidth() {
		return $this->width;
	}


	/**
	 * @param string $css_class
	 */
	public function setCssClass($css_class) {
		$this->css_class = $css_class;
	}


	/**
	 * @return string
	 */
	public function getCssClass() {
		return $this->css_class;
	}


	/**
	 * @param int $minimum_input_length
	 */
	public function setMinimumInputLength($minimum_input_length) {
		$this->minimum_input_length = $minimum_input_length;
	}


	/**
	 * @return int
	 */
	public function getMinimumInputLength() {
		return $this->minimum_input_length;
	}


	/**
	 * @param string $ajax_link setting the ajax link will lead to ignoration of the 'setOptions' function as the link given will be used to get the
	 */
	public function setAjaxLink($ajax_link) {
		$this->ajax_link = $ajax_link;
	}


	/**
	 * @return string
	 */
	public function getAjaxLink() {
		return $this->ajax_link;
	}


	/**
	 * @param \srDefaultAccessChecker $access_checker
	 */
	public function setAccessChecker($access_checker) {
		$this->access_checker = $access_checker;
	}


	/**
	 * @return \srDefaultAccessChecker
	 */
	public function getAccessChecker() {
		return $this->access_checker;
	}


	/**
	 * @param \ilTemplate $input_template
	 */
	public function setInputTemplate($input_template) {
		$this->input_template = $input_template;
	}


	/**
	 * @return \ilTemplate
	 */
	public function getInputTemplate() {
		return $this->input_template;
	}


	/**
	 * This implementation might sound silly. But the multiple select input used parses the post vars differently if you use ajax. thus we have to do
	 * this stupid 'trick'. Shame on select2 project ;)
	 *
	 * @return string the real postvar.
	 */
	protected function searchPostVar() {
		if (substr($this->getPostVar(), - 2) == '[]') {
			return substr($this->getPostVar(), 0, - 2);
		} else {
			return $this->getPostVar();
		}
	}


	/**
	 * @param array $array
	 */
	public function setValueByArray($array) {
		$val = $array[$this->searchPostVar()];
		if (is_array($val)) {
			$val;
		} elseif (!$val) {
			$val = array();
		} else {
			$val = explode(',', $val);
		}
		$this->setValue($val);
	}

	protected function escapePostVar($postVar) {
		$postVar = $this->stripLastStringOccurrence($postVar, "[]");
		$postVar = str_replace("[", '\\\\[', $postVar);
		$postVar = str_replace("]", '\\\\]', $postVar);
		return $postVar;
	}

	/**
	 * @param $text string
	 * @param $string string
	 * @return string
	 */
	private function stripLastStringOccurrence($text, $string) {
		$pos = strrpos($text, $string);
		if($pos !== false) {
			$text = substr_replace($text, "", $pos, strlen($string));
		}
		return $text;
	}
}