import * as element from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { hot } from 'react-hot-loader/root';
import { ErrorMessage, Field, Form, Formik } from 'formik';
import * as Yup from 'yup';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import PulseLoader, { use } from 'react-spinners/PulseLoader';
import Notification from '../components/Notification';

const pluginSlug = 'expertstatsplugin';

const SettingsSchema = Yup.object().shape({
	import_mode: Yup.string(),
	email: Yup.string()
		.email('Invalid email')
		.required('Required'),
	password: Yup.string()
		.required('Required')
});

const SettingsError = ({ name }) => {
	return <ErrorMessage name={name} component="div" className="error settings-error"/>
};

function saveSettings (data, props) {
	let postData = {};

	delete data.login;

	postData[ pluginSlug ] = new Buffer(JSON.stringify(data)).toString('base64');
	apiFetch({
		path: '/wp/v2/settings',
		method: 'POST',
		data: postData
	}).catch((e) => {
		let errors = Object.assign(e.message.message);
		if (e.additional_errors && e.additional_errors.length) {
			e.additional_errors.forEach((item) => {
				errors = Object.assign(errors, item.message.message)
			})
		}

		let finalErrors = {};

		for (let error in errors) {
			if (!errors.hasOwnProperty(error)) {
				continue;
			}
			let fieldName = error.replace(`invalid_${pluginSlug}_`, '');
			props.setFieldError(fieldName, errors[ error ]);
		}
	})
		.then(() => {
			props.setSubmitting(false)
			props.setStatus(true)
		});
}

const App = hot(() => {
	return (
		<Formik initialValues={Object.assign(codeable_stats_settings, { login: null })} onSubmit={saveSettings}
						validationSchema={SettingsSchema}
						validateOnChange={false} validateOnBlur={false} component={SettingsForm}/>
	)
});

const SettingsForm = ({ isSubmitting, status }) => {
	return (
		<Form>
			{status && !isSubmitting ? (<Notification>{__('Settings saved.', pluginSlug)}</Notification>) : ''}
			<table className="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label htmlFor="import_mode">{__('Scan method', pluginSlug)}</label>
						</th>
						<td>
							<SettingsError name="import_mode"/>
							<Field component="select" name="import_mode">
								<option
									value="all">{__('Stop if the transaction id is found (use this if you want to update your data of first time fetch)', pluginSlug)}</option>
								<option
									value="stop_first">{__('Check everything (use this if you got a time out while fetching)', pluginSlug)}</option>
							</Field>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<SettingsError name="login"/>

							<label htmlFor="email">{__('E-Mail', pluginSlug)}</label>
						</th>
						<td>
							<SettingsError name="email"/>
							<Field type="text" name="email"/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label htmlFor="password">{__('Password', pluginSlug)}</label>
						</th>
						<td>
							<SettingsError name="password"/>
							<Field type="password" name="password"/>
						</td>
					</tr>
				</tbody>
			</table>
			<div className="action-buttons">
				<div className="loading">
					<PulseLoader loading={isSubmitting}/>
				</div>
				<button type="submit"
								className="button-primary" disabled={isSubmitting}>{__('Save Changes', pluginSlug)}</button>
			</div>
		</Form>)
}

domReady(() => {
	element.render(
		<App/>,
		document.getElementById('app')
	);
});
