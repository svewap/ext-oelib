<?php
return [
    // Interfaces
    'Tx_Oelib_Interface_Address' => \OliverKlee\Oelib\Interfaces\Address::class,
    'Tx_Oelib_Interface_ConfigurationCheckable' => \OliverKlee\Oelib\Interfaces\ConfigurationCheckable::class,
    'Tx_Oelib_Interface_GeocodingLookup' => \OliverKlee\Oelib\Interfaces\GeocodingLookup::class,
    'Tx_Oelib_Interface_Geo' => \OliverKlee\Oelib\Interfaces\Geo::class,
    'Tx_Oelib_Interface_Identity' => \OliverKlee\Oelib\Interfaces\Identity::class,
    'Tx_Oelib_Interface_LoginManager' => \OliverKlee\Oelib\Interfaces\LoginManager::class,
    'Tx_Oelib_Interface_MailRole' => \OliverKlee\Oelib\Interfaces\MailRole::class,
    'Tx_Oelib_Interface_MapPoint' => \OliverKlee\Oelib\Interfaces\MapPoint::class,
    'Tx_Oelib_Interface_Sortable' => \OliverKlee\Oelib\Interfaces\Sortable::class,

    // Exception
    'Tx_Oelib_Exception_AccessDenied' => \OliverKlee\Oelib\Exception\AccessDeniedException::class,
    'Tx_Oelib_Exception_Database' => \OliverKlee\Oelib\Exception\DatabaseException::class,
    'Tx_Oelib_Exception_EmptyQueryResult' => \OliverKlee\Oelib\Exception\EmptyQueryResultException::class,
    'Tx_Oelib_Exception_NotFound' => \OliverKlee\Oelib\Exception\NotFoundException::class,

    // Frontend
    'Tx_Oelib_FrontEnd_UserWithoutCookies' => \OliverKlee\Oelib\Frontend\UserWithoutCookies::class,

    // Geocoding
    'Tx_Oelib_Geocoding_Calculcator' => \OliverKlee\Oelib\Geocoding\GeoCalculcator::class,
    'Tx_Oelib_Geocoding_Dummy' => \OliverKlee\Oelib\Geocoding\DummyGeocodingLookup::class,
    'Tx_Oelib_Geocoding_Google' => \OliverKlee\Oelib\Geocoding\GoogleGeocoding::class,

    // Mapper
    'Tx_Oelib_DataMapper' => \OliverKlee\Oelib\Mapper\AbstractDataMapper::class,
    'Tx_Oelib_Mapper_BackEndUser' => \OliverKlee\Oelib\Mapper\BackEndUserMapper::class,
    'Tx_Oelib_Mapper_BackEndUserGroup' => \OliverKlee\Oelib\Mapper\BackEndUserGroupMapper::class,
    'Tx_Oelib_Mapper_Country' => \OliverKlee\Oelib\Mapper\CountryMapper::class,
    'Tx_Oelib_Mapper_Currency' => \OliverKlee\Oelib\Mapper\CurrencyMapper::class,
    'Tx_Oelib_Mapper_FederalState' => \OliverKlee\Oelib\Mapper\FederalStateMapper::class,
    'Tx_Oelib_Mapper_FrontEndUser' => \OliverKlee\Oelib\Mapper\FrontEndUserMapper::class,
    'Tx_Oelib_Mapper_FrontEndUserGroup' => \OliverKlee\Oelib\Mapper\FrontEndUserGroupMapper::class,
    'Tx_Oelib_Mapper_Language' => \OliverKlee\Oelib\Mapper\LanguageMapper::class,
    'Tx_Oelib_MapperRegistry' => \OliverKlee\Oelib\Mapper\MapperRegistry::class,

    // Model
    'Tx_Oelib_Model' => \OliverKlee\Oelib\Model\AbstractModel::class,
    'Tx_Oelib_Model_BackEndUser' => \OliverKlee\Oelib\Model\BackEndUser::class,
    'Tx_Oelib_Model_BackEndUserGroup' => \OliverKlee\Oelib\Model\BackEndUserGroup::class,
    'Tx_Oelib_Model_Country' => \OliverKlee\Oelib\Model\Country::class,
    'Tx_Oelib_Model_Currency' => \OliverKlee\Oelib\Model\Currency::class,
    'Tx_Oelib_Model_FederalState' => \OliverKlee\Oelib\Model\FederalState::class,
    'Tx_Oelib_Model_FrontEndUser' => \OliverKlee\Oelib\Model\FrontEndUser::class,
    'Tx_Oelib_Model_FrontEndUserGroup' => \OliverKlee\Oelib\Model\FrontEndUserGroup::class,
    'Tx_Oelib_Model_Language' => \OliverKlee\Oelib\Model\Language::class,

    // View Helpers
    'Tx_Oelib_ViewHelpers_GoogleMapsViewHelper' => \OliverKlee\Oelib\ViewHelpers\GoogleMapsViewHelper::class,
    'Tx_Oelib_ViewHelper_Price' => \OliverKlee\Oelib\ViewHelpers\PriceViewHelper::class,
    'Tx_Oelib_ViewHelpers_UppercaseViewHelper' => \OliverKlee\Oelib\ViewHelpers\UppercaseViewHelper::class,

    // Visibility
    'Tx_Oelib_Visibility_Node' => \OliverKlee\Oelib\Visibility\Node::class,
    'Tx_Oelib_Visibility_Tree' => \OliverKlee\Oelib\Visibility\Tree::class,

    // DataStructures
    'Tx_Oelib_Object' => \OliverKlee\Oelib\DataStructures\AbstractObjectWithAccessors::class,
    'Tx_Oelib_PublicObject' => \OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors::class,
    'Tx_Oelib_List' => \OliverKlee\Oelib\DataStructures\Collection::class,
];
