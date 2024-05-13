<?php
namespace Sil\SilAuth\models;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Sil\SilAuth\auth\Authenticator;
use Sil\SilAuth\behaviors\CreatedAtUtcBehavior;
use Sil\SilAuth\http\Request;
use Sil\SilAuth\time\UtcTime;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use Yii;

class FailedLoginIpAddress extends FailedLoginIpAddressBase implements LoggerAwareInterface
{
    use \Sil\SilAuth\traits\LoggerAwareTrait;
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'ip_address' => Yii::t('app', 'IP Address'),
            'occurred_at_utc' => Yii::t('app', 'Occurred At (UTC)'),
        ]);
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => CreatedAtUtcBehavior::className(),
                'attributes' => [
                    Model::EVENT_BEFORE_VALIDATE => 'occurred_at_utc',
                ],
            ],
        ];
    }
    
    public static function countRecentFailedLoginsFor($ipAddress)
    {
        return self::find()->where([
            'ip_address' => strtolower($ipAddress),
        ])->andWhere([
            '>=', 'occurred_at_utc', UtcTime::format('-60 minutes')
        ])->count();
    }
    
    public static function getFailedLoginsFor($ipAddress)
    {
        if ( ! Request::isValidIpAddress($ipAddress)) {
            throw new \InvalidArgumentException(sprintf(
                '%s is not a valid IP address.',
                var_export($ipAddress, true)
            ));
        }
        
        return self::findAll(['ip_address' => strtolower($ipAddress)]);
    }
    
    /**
     * Get the most recent failed-login record for the given IP address, or null
     * if none is found.
     *
     * @param string $ipAddress The IP address.
     * @return FailedLoginIpAddress|null
     */
    public static function getMostRecentFailedLoginFor($ipAddress)
    {
        return self::find()->where([
            'ip_address' => strtolower($ipAddress),
        ])->orderBy([
            'occurred_at_utc' => SORT_DESC,
        ])->one();
    }
    
    /**
     * Get the number of seconds remaining until the specified IP address is
     * no longer blocked by a rate-limit. Returns zero if it is not currently
     * blocked.
     * 
     * @param string $ipAddress The IP address in question
     * @return int The number of seconds
     */
    public static function getSecondsUntilUnblocked($ipAddress)
    {
        $failedLogin = self::getMostRecentFailedLoginFor($ipAddress);
        
        return Authenticator::getSecondsUntilUnblocked(
            self::countRecentFailedLoginsFor($ipAddress),
            $failedLogin->occurred_at_utc ?? null
        );
    }
    
    public function init()
    {
        $this->initializeLogger();
        parent::init();
    }
    
    public static function isCaptchaRequiredFor($ipAddress)
    {
        return Authenticator::isEnoughFailedLoginsToRequireCaptcha(
            self::countRecentFailedLoginsFor($ipAddress)
        );
    }
    
    public static function isCaptchaRequiredForAnyOfThese(array $ipAddresses)
    {
        foreach ($ipAddresses as $ipAddress) {
            if (self::isCaptchaRequiredFor($ipAddress)) {
                return true;
            }
        }
        return false;
    }
    
    public static function isRateLimitBlocking($ipAddress)
    {
        $secondsUntilUnblocked = self::getSecondsUntilUnblocked($ipAddress);
        return ($secondsUntilUnblocked > 0);
    }
    
    public static function isRateLimitBlockingAnyOfThese($ipAddresses)
    {
        foreach ($ipAddresses as $ipAddress) {
            if (self::isRateLimitBlocking($ipAddress)) {
                return true;
            }
        }
        return false;
    }
    
    public static function recordFailedLoginBy(
        array $ipAddresses,
        LoggerInterface $logger
    ) {
        foreach ($ipAddresses as $ipAddress) {
            $newRecord = new FailedLoginIpAddress(['ip_address' => strtolower($ipAddress)]);
            
            if ( ! $newRecord->save()) {
                $logger->critical(json_encode([
                    'event' => 'Failed to update login attempts counter in '
                    . 'database, so unable to rate limit that IP address.',
                    'ipAddress' => $ipAddress,
                    'errors' => $newRecord->getErrors(),
                ]));
            }
        }
    }
    
    public static function resetFailedLoginsBy(array $ipAddresses)
    {
        foreach ($ipAddresses as $ipAddress) {
            self::deleteAll(['ip_address' => strtolower($ipAddress)]);
        }
    }
}
