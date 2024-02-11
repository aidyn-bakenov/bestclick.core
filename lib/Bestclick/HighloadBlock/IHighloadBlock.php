<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 27.05.2022 15:12
 */

namespace Bestclick\HighloadBlock;

interface IHighloadBlock
{
    public static function getTableName();

    public static function getMap();

    public static function add(array $data);

    public static function update($primary, array $data);

    public static function delete($primary);

    public static function getBlockId(): int;

    public static function getUserFieldByName(string $fieldName): array;

    public static function getUserFieldListByNames(array $arFieldNames): array;
}