import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import Notification from '../../components/Notification';


const pluginSlug = window.codeable_stats_config.slug;

export default class DeletedDataNotification extends Component {
	constructor (props) {
		super(props);
		this.state = { deleted: props.deleted };
	}
	render () {
		if (this.state.deleted) {
			return (<Notification>{__('Data successfully deleted.', pluginSlug)}</Notification>);
		}

		return (null);
	}
}
