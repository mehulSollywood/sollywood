<?php

namespace App\Utils;

use App\Models\User;
use Log;
use Illuminate\Support\Arr;

/**
 * Class LogUtil'logs'
 * @package App\Utils
 */
class LogUtil
{

    /**
     * @var double $requestTime Время выполнения запроса
     */
    public static $requestTime = 0;

    /**
     * @var \Illuminate\Http\Request|null $request Запрос
     */
    public static $request = null;

    /**
     * @var null|\App\Models\User $user Авторизированный пользователь
     */
    private static $user = null;

    /**
     * @var string $format Формат логов
     *
     * 1.   STATUS - SUCCESS|FAIL
     * 2.   STATUS_CODE - HTTP code
     * 3.   IP - IP address
     * 4.   METHOD - OPTIONS|GET|POST|PUT|DELETE
     * 5.   PATH_INFO - URL|URI
     * 6.   MESSAGE - $response['message'] текст
     * 7.   CODE - $response['code'] код ответа
     * 8.   REQUEST_TIME - время выполнения запроса
     * 9.   ID_USER - ID авторизованного пользователя
     * 10.  USER_AGENT - описание браузера клиента
     *
     * CONTEXT - {'request' => [данные в запросе], 'response' => [данные в ответе]}
     *
     * STATUS STATUS_CODE IP METHOD PATH_INFO "MESSAGE" CODE REQUEST_TIME ID_USER COMPANY_TIN PERSON_TIN "PERSON_NAME" "PERSON_SURNAME" "PERSON_PATRONYMIC" "USER_AGENT" "TYPE_LOG" CONTEXT:{'request' => [], 'response' => []}
     */
    private static $format = [
        'STATUS' => '%s',
        'STATUS_CODE' => '%s',
        'IP' => '%s',
        'METHOD' => '%s',
        'PATH_INFO' => '%s',
        'MESSAGE' => '"%s"',
        'CODE' => '"%s"',
        'REQUEST_TIME' => '%s',
        'ID_USER' => '%s',
        'COMPANY' => '%s',
        'SEEKER_PROFILE' => '%s',
        'USER_AGENT' => '"%s"'
    ];

    /**
     * @var array $context содержит контекст логов
     */
    private static $context = [
        'request' => [],
        'response' => []
    ];

    /**
     * Обнуление переменных, если во время работы приложения планируется ещё раз писать логи
     *
     * @return bool
     */
    protected static function init(): bool
    {
        self::$context = [
            'request' => [],
            'response' => []
        ];

        return true;
    }

    /**
     * Подготовка строки для лога
     *
     * @param array $data Данные
     * @return string
     */
    protected static function prepareFormat(array $data): string
    {
        return vsprintf(implode(' ', self::$format), $data);
    }

    /**
     * Получение конфигов
     *
     * @param string $param
     * @return mixed
     */
    public static function getConfig(string $param)
    {
        return config('logging')[$param];
    }

    /**
     * Установка контекста
     *
     * @param String $field
     * @param $data
     * @return bool
     */
    public static function setContext(String $field, $data): bool
    {
        if (array_key_exists($field, self::$context)) {
            self::$context[$field] = $data;
            return true;
        }

        return false;
    }

    /**
     * Подготавливает строку всех ошибок через запятую
     *
     * @param array $data
     * @return String
     */
    public static function prepareCodes($data): String
    {
        if (isset($data['success']) && !$data['success']) {
            $codes = Arr::where(Arr::dot($data), function ($value) {
                return is_numeric($value);
            });

            asort($codes);

            $codes = implode(',', array_unique($codes));
        }

        return $codes ?? 'null';
    }

    /**
     * Если пользователь не был назначен, берем его с приложения
     *
     * @return User|null
     */
    public static function resolveUser()
    {
        if (!self::$user) {
            self::$user = App()->make('request')->user();
        }

        return self::$user;
    }

    /**
     * Устанавливаем пользователя, полезно, если вызван метод авторизации и пользователь ещё не авторизован
     *
     * @param User $user
     * @return User|null
     */
    public static function setUser(User $user)
    {
        self::$user = $user;

        return $user;
    }

    /**
     * Запись лога в файл
     *
     * @param array $data
     * @return bool
     */
    public static function Log(array $data): bool
    {
        if (self::getConfig('app_info_log_enable')) {
            Log::getHandlers()[0]->getFormatter()->allowInlineLineBreaks(false);
            Log::info(self::prepareFormat($data), self::$context);
            Log::getHandlers()[0]->getFormatter()->allowInlineLineBreaks(true);
            self::init();

            return true;
        }

        return false;
    }

}
