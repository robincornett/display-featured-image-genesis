/*
 * Copyright (c) 2019 Robin Cornett
 */

( function ( wp, $, undefined ) {
	'use strict';
	const DFIGBlockObject = {
		el: wp.element.createElement,
	};

	/**
	 * Initialize and register the block.
	 */
	DFIGBlockObject.init = function ( params ) {
		const registerBlockType = wp.blocks.registerBlockType,
			ServerSideRender = wp.components.ServerSideRender,
			InspectorControls = wp.blockEditor.InspectorControls;

		registerBlockType( params.block, {
			title: params.title,
			description: params.description,
			keywords: params.keywords,
			icon: params.icon,
			category: params.category,
			supports: {
				html: false
			},

			getEditWrapperProps ( { blockAlignment, alignment } ) {
				return {
					'data-align': blockAlignment,
					'alignment': alignment
				};
			},

			edit: props => {
				const {
					attributes,
					setAttributes
				} = props,
					Fragment = wp.element.Fragment,
					BlockControls = wp.blockEditor.BlockControls,
					BlockAlignmentToolbar = wp.blockEditor.BlockAlignmentToolbar,
					AlignmentToolbar = wp.blockEditor.AlignmentToolbar;
				let render = DFIGBlockObject.el( ServerSideRender, {
					block: params.block,
					attributes: attributes
				} );
				if ( params.placeholder && !attributes[ params.required ] ) {
					render = DFIGBlockObject.el( 'div', {
						className: DFIGBlockObject.params.prefix + '-placeholder',
					}, params.placeholder );
				}

				return [
					render,
					DFIGBlockObject.el( Fragment, null,
						DFIGBlockObject.el( BlockControls, null,
							DFIGBlockObject.el( BlockAlignmentToolbar, {
								value: attributes.blockAlignment,
								controls: [ 'wide', 'full' ],
								onChange: ( value ) => {
									setAttributes( { blockAlignment: value } );
								},
							} ),
							DFIGBlockObject.el( AlignmentToolbar, {
								value: attributes.alignment,
								controls: [ 'left', 'right', 'center' ],
								onChange: ( value ) => {
									setAttributes( { alignment: value } );
								},
							} )
						),
					),
					DFIGBlockObject.el( InspectorControls, {},
						_getPanels( props, params ),
						onChangeSelect( false, false, props )
					)
				];
			},

			save: props => {
				return null;
			},
		} );
	};

	/**
	 * Get the panels for the block controls.
	 *
	 * @param props
	 * @param params
	 * @return {Array}
	 * @private
	 */
	function _getPanels ( props, params ) {
		const panels = [],
			PanelBody = wp.components.PanelBody;
		Object.keys( params.panels ).forEach( function ( key, index ) {
			if ( params.panels.hasOwnProperty( key ) ) {
				const IndividualPanel = params.panels[ key ];
				panels[ index ] = DFIGBlockObject.el( PanelBody, {
					title: IndividualPanel.title,
					initialOpen: IndividualPanel.initialOpen,
					className: DFIGBlockObject.params.prefix + '-panel-' + key
				}, _getControls( props, IndividualPanel.attributes ) );
			}
		} );

		return panels;
	}

	/**
	 * Get all of the block controls, with defaults and options.
	 *
	 * @param props
	 * @param fields
	 * @return {Array}
	 * @private
	 */
	function _getControls ( props, fields ) {
		const controls = [];
		Object.keys( fields ).forEach( function ( key, index ) {
			if ( fields.hasOwnProperty( key ) ) {
				var skipped = [ 'blockAlignment', 'className', 'alignment' ];
				if ( -1 !== skipped.indexOf( key ) ) {
					return;
				}
				const IndividualField = fields[ key ],
					control = _getControlType( IndividualField.method, IndividualField.type );
				controls[ index ] = DFIGBlockObject.el( control, _getIndividualControl( key, IndividualField, props ) );
			}
		} );

		return controls;
	}

	/**
	 * Get the control type.
	 * @param method
	 * @param control_type
	 * @return {*}
	 * @private
	 */
	function _getControlType ( method, control_type ) {
		const {
			TextControl,
			SelectControl,
			CheckboxControl,
			TextareaControl
		} = wp.components;
		const control = TextControl;
		if ( 'select' === method ) {
			return SelectControl;
		} else if ( 'checkbox' === method ) {
			return CheckboxControl;
		} else if ( 'textarea' === method ) {
			return TextareaControl;
		}

		return control;
	}

	/**
	 * Build the individual control object. Sets up standard properties for all
	 * controls; then adds custom properties as needed.
	 *
	 * @param key
	 * @param field
	 * @param props
	 * @return {{label: *, value: *, className: string, onChange: onChange}}
	 * @private
	 */
	function _getIndividualControl ( key, field, props ) {
		const { attributes, setAttributes } = props;
		const control = {
			label: field.label,
			value: attributes[ key ],
			className: DFIGBlockObject.params.prefix + '-' + key,
			onChange: ( value ) => {
				if ( 'taxonomy' === key ) {
					onChangeSelect( key, value, props );
				}
				setAttributes( { [ key ]: value } );
			}
		};

		if ( 'select' === field.method ) {
			control.options = field.options;
		} else if ( 'checkbox' === field.method ) {
			control.checked = attributes[ key ];
		}

		return control;
	}

	/**
	 * Update values and options.
	 * @param select_id
	 * @param value
	 * @param props
	 */
	function onChangeSelect ( select_id, value, props ) {
		if ( DFIGBlockObject.params.prefix + '/term' !== props.name ) {
			return;
		}
		const data = _getAjaxData( select_id, value, props );
		_doAjaxUpdate( data, select_id, props );
	}

	/**
	 *
	 * @param select_id
	 * @param value
	 * @param props
	 * @returns {{action: string, security: *}}
	 * @private
	 */
	function _getAjaxData ( select_id, value, props ) {
		const data = {
			action: DFIGBlockObject.params.prefix + '_block',
			security: DFIGBlockObject.params.security
		},
			{ attributes } = props;
		if ( 'taxonomy' === select_id ) {
			data.taxonomy = value;
		} else {
			data.taxonomy = attributes.taxonomy;
		}

		return data;
	}

	/**
	 * Call on our ajax action and update the select
	 * @param data
	 * @param select_id
	 * @param props
	 * @return
	 * @private
	 */
	function _doAjaxUpdate ( data, select_id, props ) {
		const { attributes, setAttributes } = props;
		$.post( DFIGBlockObject.params.ajax_url, data, function ( response ) {

			if ( undefined !== response.success && false === response.success ) {
				return false;
			}

			const jsonData = $.parseJSON( response );

			_modifySelectInput( jsonData, 'term', attributes );
			if ( select_id ) {
				setAttributes( {
					term: '',
				} );
			}
		} );
	}

	/**
	 * Modify the term dropdown.
	 * @param options
	 * @param key
	 * @param attributes
	 * @private
	 */
	function _modifySelectInput ( options, key, attributes ) {
		const selectID = $( '.' + DFIGBlockObject.params.prefix + '-' + key + ' select' ),
			oldValue = attributes[ key ] || '';
		selectID.empty();
		_updateSelectOptions( options, selectID, oldValue );
	}

	/**
	 * Update the select input with the new options.
	 * @param options
	 * @param selectID
	 * @param oldValue
	 * @private
	 */
	function _updateSelectOptions ( options, selectID, oldValue ) {
		$.each( options, function ( key, value ) {
			const new_option = $( '<option />' )
				.val( key ).text( value ),
				method = !key ? 'prepend' : 'append';
			selectID.val( oldValue );
			selectID[ method ]( new_option );
		} );
	}

	DFIGBlockObject.params = typeof DisplayFeaturedImageBlock === 'undefined' ? '' : DisplayFeaturedImageBlock;

	if ( typeof DFIGBlockObject.params !== 'undefined' ) {
		Object.keys( DFIGBlockObject.params.blocks ).forEach( function ( key, index ) {
			if ( DFIGBlockObject.params.blocks.hasOwnProperty( key ) ) {
				DFIGBlockObject.init( DFIGBlockObject.params.blocks[ key ] );
			}
		} );
	}
} )( wp, jQuery );
