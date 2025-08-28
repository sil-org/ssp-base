<?php

namespace SimpleSAML\Module\silauth\Auth\Source\models;

use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SimpleSAML\Module\silauth\Auth\Source\auth\Authenticator;
use SimpleSAML\Module\silauth\Auth\Source\behaviors\CreatedAtUtcBehavior;
use SimpleSAML\Module\silauth\Auth\Source\time\UtcTime;
use SimpleSAML\Module\silauth\Auth\Source\traits\LoggerAwareTrait;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class FailedLoginUsername extends FailedLoginUsernameBase implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'occurred_at_utc' => Yii::t('app', 'Occurred At (UTC)'),
        ]);
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => CreatedAtUtcBehavior::class,
                'attributes' => [
                    Model::EVENT_BEFORE_VALIDATE => 'occurred_at_utc',
                ],
            ],
        ];
    }

    /**
     * @throws Exception
     */
    public static function countRecentFailedLoginsFor(string $username): int
    {
        $count = self::find()->where([
            'username' => mb_strtolower($username),
        ])->andWhere([
            '>=', 'occurred_at_utc', UtcTime::format('-60 minutes')
        ])->count();
        if (!is_numeric($count)) {
            throw new Exception('expected a numeric value for recent failed logins by username, got ' . $count);
        }
        return (int)$count;
    }

    /**
     * Find the records with the given username (if any).
     *
     * @param string $username The username.
     * @return FailedLoginUsername[] An array of any matching records.
     */
    public static function getFailedLoginsFor(string $username): array
    {
        return self::findAll(['username' => mb_strtolower($username)]);
    }

    /**
     * Get the most recent failed-login record for the given username, or null
     * if none is found.
     *
     * @param string $username The username.
     * @return FailedLoginUsername|null
     */
    public static function getMostRecentFailedLoginFor(string $username): ?FailedLoginUsername
    {
        return self::find()->where([
            'username' => mb_strtolower($username),
        ])->orderBy([
            'occurred_at_utc' => SORT_DESC,
        ])->one();
    }

    /**
     * Get the number of seconds remaining until the specified username is
     * no longer blocked by a rate-limit. Returns zero if the user is not
     * currently blocked.
     *
     * @param string $username The username in question
     * @return int The number of seconds
     * @throws Exception
     */
    public static function getSecondsUntilUnblocked(string $username): int
    {
        $failedLogin = self::getMostRecentFailedLoginFor($username);

        return Authenticator::getSecondsUntilUnblocked(
            self::countRecentFailedLoginsFor($username),
            $failedLogin->occurred_at_utc ?? null
        );
    }

    public function init(): void
    {
        $this->initializeLogger();
        parent::init();
    }

    /**
     * Find out whether a rate limit is blocking the specified username.
     *
     * @param string $username The username
     * @return bool
     * @throws Exception
     */
    public static function isRateLimitBlocking(string $username): bool
    {
        $secondsUntilUnblocked = self::getSecondsUntilUnblocked($username);
        return ($secondsUntilUnblocked > 0);
    }

    /**
     * @throws Exception
     */
    public static function isCaptchaRequiredFor(?string $username): bool
    {
        if (empty($username)) {
            return false;
        }
        return Authenticator::isEnoughFailedLoginsToRequireCaptcha(
            self::countRecentFailedLoginsFor($username)
        );
    }

    /**
     * @throws \yii\db\Exception
     */
    public static function recordFailedLoginBy(
        string          $username,
        LoggerInterface $logger
    ): void {
        $newRecord = new FailedLoginUsername(['username' => mb_strtolower($username)]);
        if (!$newRecord->save()) {
            $logger->critical(json_encode([
                'event' => 'Failed to update login attempts counter in '
                    . 'database, so unable to rate limit that username.',
                'username' => $username,
                'errors' => $newRecord->getErrors(),
            ]));
        }
    }

    public static function resetFailedLoginsBy(string $username): void
    {
        self::deleteAll(['username' => mb_strtolower($username)]);
    }
}
