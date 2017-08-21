<?php
namespace Wenpeng\Validator;

class Validator
{
    /**
     * 错误信息
     *
     * @var string
     */
    private $error = '';

    /**
     * 原始数据
     *
     * @var array
     */
    private $source = array();

    /**
     * 验证规则
     * @var array
     */
    private $rules = array();

    /**
     * 自定义消息
     * @var array
     */
    private $messages = array();

    /**
     * 构造函数
     * @param array $source     原始数据
     * @param array $rules      验证规则
     * @param array $messages   错误消息
     */
    public function __construct($source, $rules, $messages = [])
    {
        $this->source = $source;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    /**
     * 开始验证
     * @param array $source     原始数据
     * @param array $rules      验证规则
     * @param array $messages   错误消息
     * @return Validator
     */
    public static function make($source, $rules, $messages)
    {
        return new static($source, $rules, $messages);
    }

    /**
     * 检查验证结果
     * @return bool
     */
    public function fail()
    {
        foreach ($this->rules as $field => $string) {
            $rules = explode('|', $string);
            if (isset($this->source[$field])) {
                foreach ($rules as $rule) {
                    if ($rule === 'require') {
                        continue;
                    }
                    $rule = $this->parseRuleString($rule);
                    if ($rule === false) {
                        return true;
                    }
                    $result = call_user_func_array(
                        $rule['callable'],
                        array_merge(
                            array($this->source[$field]),
                            $rule['arguments']
                        )
                    );
                    if ((bool) $result === false) {
                        $this->setError($field, $rule['ruleName']);
                        return true;
                    }
                }
            } else {
                if (in_array('require', $rules)) {
                    $this->setError($field, 'require');
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 分析验证规则
     * @param string $string 规则字符串
     * @return array|false
     */
    private function parseRuleString($string)
    {
        $rule = array(
            'callable' => $string,
            'arguments' => array()
        );
        if (strpos($string, ':') > 0) {
            $array = explode(':', $string, 2);
            if (isset($array[1]) && strpos($array[1], ',')) {
                $rule['arguments'] = explode(',', $array[1]);
            }
            $rule['callable'] = $array[0];
        }

        $rule['ruleName'] = $rule['callable'];

        if (strpos($rule['callable'], '@') > 0) {
            $rule['callable'] = explode('@', $rule['callable'], 2);
        }

        if (is_callable($rule['callable']) === false) {
            $this->error = '规则 '. $rule['ruleName'] .' 不可用';
            return false;
        }

        return $rule;
    }

    /**
     * 设置错误消息
     * @param string $field 数据键名
     * @param string $rule  验证规则
     * @return void
     */
    private function setError($field, $rule)
    {
        $index = $field .'.'. $rule;
        if (isset($this->messages[$index])) {
            $this->error = $this->messages[$index];
        } else {
            $this->error = '字段 '.$field.' 未通过 '.$rule.' 验证';
        }
    }

    /**
     * 读取错误消息
     * @return string 错误消息
     */
    public function error()
    {
        return $this->error;
    }
}