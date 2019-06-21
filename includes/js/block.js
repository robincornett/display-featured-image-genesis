/*
 * Copyright (c) 2019 Robin Cornett
 */

(function ( wp, undefined ) {
	'use strict';
	const DisplayFeaturedImageBlockObject = {
		el: wp.element.createElement,
	};

	DisplayFeaturedImageBlockObject.takeTwo = function ( block ) {
		const registerBlockType = wp.blocks.registerBlockType,
		      ServerSideRender  = wp.components.ServerSideRender,
		      InspectorControls = wp.blockEditor.InspectorControls;
		const data = DisplayFeaturedImageBlockObject.params[block];
		// console.log( data );
		registerBlockType( data.block, {
			title: data.title,
			description: data.description,
			keywords: data.keywords,
			icon: data.icon,
			category: data.category,
			supports: {
				html: false
			},

			getEditWrapperProps( {blockAlignment} ) {
				return {'data-align': blockAlignment};
			},

			edit: props => {
				const {
					      attributes,
					      setAttributes
				      }                     = props,
				      Fragment              = wp.element.Fragment,
				      BlockControls         = wp.blockEditor.BlockControls,
				      BlockAlignmentToolbar = wp.blockEditor.BlockAlignmentToolbar;
				return [
					DisplayFeaturedImageBlockObject.el( ServerSideRender, {
						block: data.block,
						attributes: data.attributes
					} ),
					DisplayFeaturedImageBlockObject.el( Fragment, null,
						DisplayFeaturedImageBlockObject.el( BlockControls, null,
							DisplayFeaturedImageBlockObject.el( BlockAlignmentToolbar, {
								value: props.attributes.blockAlignment,
								controls: ['wide', 'full'],
								onChange: ( value ) => {
									setAttributes( {blockAlignment: value} );
								},
							} )
						),
					),
					DisplayFeaturedImageBlockObject.el( InspectorControls, {},
						_getPanels( props, block )
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
	 * @param block
	 * @return {Array}
	 * @private
	 */
	function _getPanels( props, block ) {
		const panels    = [],
		      PanelBody = wp.components.PanelBody;
		const blockData = DisplayFeaturedImageBlockObject.params[block];
		Object.keys( blockData.panels ).forEach( function ( key, index ) {
			if ( blockData.panels.hasOwnProperty( key ) ) {
				const IndividualPanel = blockData.panels[key];
				panels[index] = DisplayFeaturedImageBlockObject.el( PanelBody, {
					title: IndividualPanel.title,
					initialOpen: IndividualPanel.initialOpen
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
	function _getControls( props, fields ) {
		const controls = [];
		Object.keys( fields ).forEach( function ( key, index ) {
			if ( fields.hasOwnProperty( key ) ) {
				var skipped = [ 'blockAlignment', 'className' ];
				if ( -1 !== skipped.indexOf( key ) ) {
					return;
				}
				const IndividualField = fields[key],
				      control         = _getControlType( IndividualField.method, IndividualField.type );
				controls[index] = DisplayFeaturedImageBlockObject.el( control, _getIndividualControl( key, IndividualField, props ) );
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
	function _getControlType( method, control_type ) {
		const {
			      TextControl,
			      SelectControl,
			      RangeControl,
			      CheckboxControl,
			      TextareaControl
		      } = wp.components;
		const control = TextControl;
		if ( 'select' === method ) {
			return SelectControl;
		} else if ( 'number' === method && 'number' === control_type ) {
			return RangeControl;
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
	function _getIndividualControl( key, field, props ) {
		const {attributes, setAttributes} = props;
		const control = {
			heading: field.heading,
			label: field.label,
			value: attributes[key],
			className: 'displayfeaturedimagegenesis-' + key,
			onChange: ( value ) => {
				setAttributes( {[key]: value} );
			}
		};

		if ( 'select' === field.method ) {
			control.options = field.options;
		} else if ( 'number' === field.method ) {
			control.min = field.min;
			control.max = field.max;
			if ( 'number' !== field.type ) {
				control.type = 'number';
			} else {
				control.initialPosition = field.min;
			}
		} else if ( 'checkbox' === field.method ) {
			control.checked = attributes[key];
		}

		return control;
	}

	DisplayFeaturedImageBlockObject.params = typeof DisplayFeaturedImageGenesisBlock === 'undefined' ? '' : DisplayFeaturedImageGenesisBlock;

	if ( typeof DisplayFeaturedImageBlockObject.params !== 'undefined' ) {
		DisplayFeaturedImageBlockObject.takeTwo( 'post-type' );
	}
} )( wp );
