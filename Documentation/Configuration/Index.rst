

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Configuration
-------------

See the tutorial how to change your extension to use the Salutation
Switcher. In your TS Setup, set the following option for your
extension ( *not* the Salutation Switcher extension!):

::

      salutation = formal

or

::

      salutation = informal

Setting an API key for Google geocoding and Google Maps
--------------------------------------------

You can set the API code using TypoScript:

::

     plugin.tx_oelib.googleGeocodingApiKey = ...
     plugin.tx_oelib.googleMapsApiKey = ...
