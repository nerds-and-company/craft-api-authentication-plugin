<?php

namespace Craft;

/**
 * Class ApiAuth_UserKeyRecord.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @property int $id
 * @property int $userId
 * @property string $key
 * @property DateTime $expires
 */
class ApiAuth_UserKeyRecord extends BaseRecord
{
    /**
     * @inheritdoc
     */
    public function getTableName()
    {
        return 'apiauth_userkeys';
    }

    /**
     * @inheritdoc
     */
    protected function defineAttributes()
    {
        return array(
            'userId' => array(AttributeType::Number, 'required' => true),
            'key' => array(AttributeType::String, 'required' => true, 'unique' => true),
            'expires' => array(AttributeType::DateTime, 'required' => true),
        );
    }

    /**
     * @inheritdoc
     */
    public function defineRelations()
    {
        return [
            'user' => [static::BELONGS_TO, 'UserRecord', 'required' => true],
        ];
    }
}
