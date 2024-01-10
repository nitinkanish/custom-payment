
import { sprintf, __ } from '@wordpress/i18n';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting('lokipays_data', {});
console.log("setting", settings);
const defaultLabel = __(
	'Dummy Payments',
	'woo-gutenberg-products-block'
);

const label = decodeEntities(settings.title) || defaultLabel;
/**
 * Content component
 */
const Content = (props) => {
	const { eventRegistration, emitResponse } = props;
	const { onPaymentProcessing } = eventRegistration;


	React.useEffect(() => {
		const unsubscribe = onPaymentProcessing(async () => {
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
							},
					},
				};
			}

			return {
				type: emitResponse.responseTypes.ERROR,
				message: 'Please fill out required field.',
			};
		});
		// Unsubscribes when this component is unmounted.
		return () => {
			unsubscribe();
		};
	}, [
		emitResponse.responseTypes.ERROR,
		emitResponse.responseTypes.SUCCESS,
		onPaymentProcessing,
	]);

	return (
		<div class="block">
			<div class="group">
				<label>Name on Card</label>
				<input type="text" name="name_on_card" />
			</div>

			<div class="group">
				<label>Number on Card</label>
				<input type="text" name="number_on_card" />
			</div>

			<div class="group">
				<label>Month</label>
				<input type="text" name="month_on_card" />
			</div>

			<div class="group">
				<label>Year</label>
				<input type="text" name="expiry_year_on_card" />
			</div>

			<div class="group">
				<label>CVV</label>
				<input type="text" name="cvv_on_card" />
			</div>

		</div>
	);
};
/**
 * Label component
 *
 * @param {*} props Props from payment API.
 */
const Label = (props) => {
	const { PaymentMethodLabel } = props.components;
	return <PaymentMethodLabel text={label} />;
};

/**
 * Dummy payment method config object.
 */
const Dummy = {
	name: "lokipays",
	label: <Label />,
	content: <Content />,
	edit: <Content />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod(Dummy);
