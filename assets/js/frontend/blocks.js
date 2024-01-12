/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "@woocommerce/blocks-registry":
/*!******************************************!*\
  !*** external ["wc","wcBlocksRegistry"] ***!
  \******************************************/
/***/ ((module) => {

module.exports = window["wc"]["wcBlocksRegistry"];

/***/ }),

/***/ "@woocommerce/settings":
/*!************************************!*\
  !*** external ["wc","wcSettings"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wc"]["wcSettings"];

/***/ }),

/***/ "@wordpress/html-entities":
/*!**************************************!*\
  !*** external ["wp","htmlEntities"] ***!
  \**************************************/
/***/ ((module) => {

module.exports = window["wp"]["htmlEntities"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!****************************************!*\
  !*** ./resources/js/frontend/index.js ***!
  \****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @woocommerce/blocks-registry */ "@woocommerce/blocks-registry");
/* harmony import */ var _woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/html-entities */ "@wordpress/html-entities");
/* harmony import */ var _wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _woocommerce_settings__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @woocommerce/settings */ "@woocommerce/settings");
/* harmony import */ var _woocommerce_settings__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_woocommerce_settings__WEBPACK_IMPORTED_MODULE_4__);





const settings = (0,_woocommerce_settings__WEBPACK_IMPORTED_MODULE_4__.getSetting)('lokipays_data', {});
console.log("setting", settings);
const defaultLabel = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Dummy Payments', 'woo-gutenberg-products-block');
const label = (0,_wordpress_html_entities__WEBPACK_IMPORTED_MODULE_3__.decodeEntities)(settings.title) || defaultLabel;
/**
 * Content component
 */
const Content = props => {
  const {
    eventRegistration,
    emitResponse
  } = props;
  const {
    onPaymentSetup,
    onCheckoutFail,
    onCheckoutSuccess
  } = eventRegistration;
  console.log(eventRegistration);
  React.useEffect(() => {
    const successProcessing = onCheckoutSuccess(async data => {
      console.log(data);
    });
    const errorProcessing = onCheckoutFail(async data => {
      let error = "";
      try {
        error = data.processingResponse.paymentDetails.api_error;
        // Wait for error notice to be in DOM
        // TODO: find a way to set error via API.
        setTimeout(() => {
          document.querySelector(".wc-block-components-notice-banner__content div").innerHTML = error;
        }, 500);
      } catch (e) {
        // Default error message will display.
      }
    });
    const unsubscribe = onPaymentSetup(async () => {
      // Here we can do any processing we need, and then emit a response.
      // For example, we might validate a custom field, or perform an AJAX request, and then emit a response indicating it is valid or not.
      let customDataIsValid = true;
      let name = document.querySelector('input[name="name_on_card"]').value;
      let card = document.querySelector('input[name="number_on_card"]').value;
      let month = document.querySelector('input[name="month_on_card"]').value;
      let year = document.querySelector('input[name="expiry_year_on_card"]').value;
      let cvv = document.querySelector('input[name="cvv_on_card"]').value;
      if (name == '' || card == '' || month == '' || year == '' || cvv == '') {
        customDataIsValid = false;
      }
      if (customDataIsValid) {
        return {
          type: emitResponse.responseTypes.SUCCESS,
          meta: {
            paymentMethodData: {
              'name_on_card': name,
              'number_on_card': card,
              'expiry_month_on_card': month,
              'expiry_year_on_card': year,
              'cvv_on_card': cvv
            }
          }
        };
      }
      return {
        type: emitResponse.responseTypes.ERROR,
        message: 'Please fill out required field.'
      };
    });
    // Unsubscribes when this component is unmounted.
    return () => {
      unsubscribe();
      errorProcessing();
      successProcessing();
    };
  }, [emitResponse.responseTypes.ERROR, emitResponse.responseTypes.SUCCESS, onPaymentSetup]);
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    class: "block"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    class: "group"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, "Name on Card"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    name: "name_on_card"
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    class: "group"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, "Number on Card"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    name: "number_on_card"
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    class: "group"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, "Month"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    name: "month_on_card"
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    class: "group"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, "Year"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    name: "expiry_year_on_card"
  })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    class: "group"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("label", null, "CVV"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
    type: "text",
    name: "cvv_on_card"
  })));
};
/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = props => {
  const {
    PaymentMethodLabel
  } = props.components;
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PaymentMethodLabel, {
    text: label
  });
};

/**
 * Dummy payment method config object.
 */
const Dummy = {
  name: "lokipays",
  label: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Label, null),
  content: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Content, null),
  edit: (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(Content, null),
  canMakePayment: () => true,
  ariaLabel: label,
  supports: {
    features: settings.supports
  }
};
(0,_woocommerce_blocks_registry__WEBPACK_IMPORTED_MODULE_2__.registerPaymentMethod)(Dummy);
})();

/******/ })()
;
//# sourceMappingURL=blocks.js.map