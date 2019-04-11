jQuery ( document ).ready ( function ( $ ) {

    var preRequisite = $( "#sfwd-courses_course_prerequisite select" );
    var to = $( "#sfwd-courses-levels_to_percentage input" );
    var from = $( "#sfwd-courses-levels_from_percentage input" );

    /**
     * Check pre-requisite on page load
     */
    checkPreRequisite ( preRequisite.val() );

    /**
     * Check pre-requisite on change
     */
    preRequisite.change ( function () {
        var self = $( this );
        var preRequisite = self.val();
        checkPreRequisite ( preRequisite );
    });

    /**
     * Show Course level field only on child courses
     *
     * @param preRequisite
     */
    function checkPreRequisite ( preRequisite ) {
        if ( preRequisite > 0 ) {
            $( "#sfwd-courses_course_level" ).show();
        } else {
            $( "#sfwd-courses_course_level" ).hide();
        }
    }


    /**
     * Validate minimum and maximum percentage fields.
     *
     * @type {number}
     */
    var maxChars = 3;
    $( "#sfwd-courses-levels_to_percentage input, #sfwd-courses-levels_from_percentage input" ).keyup ( function( e ){
        var self = $( this );
        validate_course_level_fields ( self );
    });

    /**
     * Validate course level fields on page load
     */
    validate_course_level_fields ( to );
    validate_course_level_fields ( from );

    /**
     * To validate fields
     *
     * @param self
     */
    function validate_course_level_fields ( self ) {

        /**
         * If not on course page return
         */
        if(!self.val()) {
            return false;
        }

        /**
         * Restrict to max 3 numbers input
         */
        if ( self.val() && self.val().length >= maxChars ) {
            self.val( self.val().substr ( 0, maxChars ) );
        }

        /**
         * Validate the feilds
         */
        if ( self.val() > 100 ) {
            self.css ( { "border-color": "red" } );
            if ( !self.siblings ( ".error_message" ).length ) {
                self.parent().append( "<span class='error_message'>Value should be less than 100</span>" );
            } else {
                self.siblings( ".error_message" ).replaceWith( "<span class='error_message'>Value should be less than 100</span>" );
            }
        } else if ( self.val() < 0 ) {
            self.css ( { "border-color": "red" } );
            if ( !self.siblings( ".error_message" ).length ) {
                self.parent().append( "<span class='error_message'>Value should be greater than 0</span>" );
            } else {
                self.siblings( ".error_message" ).replaceWith( "<span class='error_message'>Value should be greater than 0</span>" );
            }
        }

        var toValue = parseInt( to.val() );
        var fromValue = parseInt( from.val() );
        if( fromValue >= toValue || toValue > 100 || fromValue < 0 ) {
            to.css ( { "border-color": "red" } );
            if ( !to.siblings ( ".error_message" ).length ) {
                to.parent().append ( "<span class='error_message'>To field's value should be greater than From field</span>" );
            } else {
                to.siblings ( ".error_message" ).replaceWith ( "<span class='error_message'>To field's value should be greater than From field</span>" );
            }
            from.css ( { "border-color": "red" } );
            if ( !from.siblings ( ".error_message" ).length ) {
                from.parent().append ( "<span class='error_message'>From field's value should be less than To field</span>" );
            } else {
                from.siblings ( ".error_message" ).replaceWith ( "<span class='error_message'>From field's value should be less than To field</span>" );
            }
        } else {
            var validated = validateWithOtherPost ( fromValue, toValue );
            if ( validated ) {
                to.css ( { "border-color": "green" } );
                to.siblings ( ".error_message" ).remove();
                from.css ( { "border-color": "green" } );
                from.siblings ( ".error_message" ).remove();
            } else {
                to.css ( { "border-color": "red" } );
                if ( !to.siblings( ".error_message" ).length ) {
                    to.parent().append ( "<span class='error_message'>Value already stored in another level</span>" );
                } else {
                    to.siblings ( ".error_message" ).replaceWith ( "<span class='error_message'>Value already stored in another level</span>" );
                }
                from.css ( { "border-color": "red" } );
                if ( !from.siblings( ".error_message" ).length ) {
                    from.parent().append ( "<span class='error_message'>Value already stored in another level</span>" );
                } else {
                    from.siblings ( ".error_message" ).replaceWith ( "<span class='error_message'>Value already stored in another level</span>" );
                }
            }
        }
    }

    /**
     * Validate field with other level post fields
     *
     * @param currentFrom
     * @param currentTo
     * @returns {boolean}
     */
    function validateWithOtherPost ( currentFrom, currentTo ) {
        var signal = true;
        $.each ( globalData.levels_data, function ( index, value ) {
            if ( index != globalData.current_post ) {
                if ( ( currentFrom >= value["sfwd-courses-levels_from_percentage"]
                    && currentFrom <= value["sfwd-courses-levels_to_percentage"] ) ||
                    ( currentTo >= value["sfwd-courses-levels_from_percentage"]
                    && currentTo <= value["sfwd-courses-levels_to_percentage"] ) ||
                    ( currentFrom <= value["sfwd-courses-levels_from_percentage"]
                    && currentTo >= value["sfwd-courses-levels_from_percentage"] ) ||
                    ( currentFrom <= value["sfwd-courses-levels_to_percentage"]
                    && currentTo >= value["sfwd-courses-levels_to_percentage"] ) ) {
                    signal =  false;
                }
            }
        });
        return signal;
    }

    $( ".post-type-sfwd-courses-levels button#contextual-help-link" ).click();

    /**
     * Open help tab on click field's question mark button
     */
    $( "#sfwd-courses-levels_from_percentage .sfwd_help_text_link, #sfwd-courses-levels_to_percentage .sfwd_help_text_link" ).click ( function() {
        $( ".post-type-sfwd-courses-levels button#contextual-help-link" ).click();
    });

    $( "#sfwd-courses-levels_from_percentage input[type=number], #sfwd-courses-levels_to_percentage input[type=number]" ).after( " %" );
});