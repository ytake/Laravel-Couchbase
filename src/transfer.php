<?php

/**
 * @param $value
 *
 * @return mixed
 */
function couchbase_php_serialize_encoder($value)
{
    return couchbase_basic_encoder_v1($value, [
        'sertype'    => COUCHBASE_SERTYPE_PHP,
        'cmprtype'   => COUCHBASE_CMPRTYPE_NONE,
        'cmprthresh' => 0,
        'cmprfactor' => 0,
    ]);
}
