import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default class Notification extends Component {
	constructor (props) {
		super(props);
		this.dismiss = this.dismiss.bind(this);
		this.state = { dismissed: false };
	}

	dismiss () {
		this.setState({ dismissed: true })
	}

	render () {
		if (this.state.dismissed) {
			return (null);
		}

		return (<div className="updated settings-error notice is-dismissible">
			<p><strong>
				{this.props.children}
			</strong></p>
			<button type="button" className="notice-dismiss" onClick={this.dismiss}><span
				className="screen-reader-text">{__('Dismiss this notice.')}</span></button>
		</div>);
	}
}
