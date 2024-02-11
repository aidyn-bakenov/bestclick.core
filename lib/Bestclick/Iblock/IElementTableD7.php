<?php
/**
 * Created by Aidyn Bakenov
 * Email: aidyn.bakenov@yandex.kz
 * 01.06.2022 16:05
 */

namespace Bestclick\Iblock;

use _CIBElement;

interface IElementTableD7
{
    public static function getIblockId(): int;

	public static function getIblockCode(): string;

    public static function getIblockApiCode(): string;

    public static function getElement(int $documentId): array;

    public static function prepareElement(_CIBElement $ob): array;

    public static function getProperties(array $codes): array;

    public static function getProperty(string $code): array;

	public static function getPropertyId(string $code): int;

    public static function getPropertyEnumList(string $propertyCode): array;

    public static function getPropertiesEnumList(array $properties): array;

    public static function getPropertyElementList(string $propertyCode): array;

    public static function getPropertiesElementList(array $properties): array;

    public static function getUsers(array $arElement): array;

    public static function getSectionUserField(string $fieldName): array;

    public static function getSectionUserFieldList(array $arFieldNames): array;

    public static function getUserFieldEnumList(int $userFieldId): array;

    public static function getUserFieldsEnumList(array $arUserFieldId): array;

    public static function prepareElementForCopy(_CIBElement $ob): array;

    public static function getChildIblockList(): array;
}