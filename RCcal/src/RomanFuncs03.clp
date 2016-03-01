;####################################################################################
;;;;;; To print out the facts use: (printout t (facts) crlf) .



;;;Rules and functions start here
(deffunction NumberOfSundays
    (?dStartDate ?dEndDate)

    (if (or (not (integerp ?dStartDate)) (not (integerp ?dEndDate))) then
        (return nil)
    )
    (if (> ?dStartDate ?dEndDate) then
        (return nil)
    )
    
    (bind ?iSundayCount 0)    
    (bind ?iDays (div (- ?dEndDate ?dStartDate) 24 60 60))
    (bind ?iWeeks (div ?iDays 7))
    (bind ?iStartDay (DoW ?dStartDate))
    (bind ?iEndDay (DoW ?dEndDate))
    (bind ?iSundayCount ?iWeeks)
    (if (> (mod ?iDays 7) 0) then
	;Cycle through remaining days
	(bind ?dTempDate (+ ?dStartDate (* ?iWeeks 7 24 60 60)))
	(bind ?dTempDate (daysAdd ?dTempDate 1)) ;Add one day, so we do not include first date.
	(while (<= ?dTempDate ?dEndDate)
	    (if (= (DoW ?dTempDate) 7) then
		(bind ?iSundayCount (+ ?iSundayCount 1))
	    )
	    (bind ?dTempDate (daysAdd ?dTempDate 1))
	)
    )
    
    ?iSundayCount
)
(deffunction pFindNewDateForSolemnities
    (?iPlannedDate ?sTypeIndex)
    
    ;We need ?*easter* defined.
    (if (not (member$ easter (get-defglobal-list))) then
	(return nil)
    )
    ;;;See the RomanReferences.md file for details on behaviour when Solemnities class with Sundays of higher rank.
    (bind ?iNewDate ?iPlannedDate)
    
    ;Check against Ash Wednesday
    (if (= ?iPlannedDate (daysAdd ?*easter* -46)) then
	(bind ?iNewDate (daysAdd ?iPlannedDate 1))
	(return ?iNewDate)
    )
    
    ;Check the special arrangements for the Annunciation
    ;  According to the third typical edition of the Roman Missal, in which is included the
    ;    Universal Norms on the Liturgical Year (UNLY) and the General Roman Calendar,
    ;    the Annunciation is moved to the Monday after the octave of Easter (UNLY, n. 60).
    (if (eq ?sTypeIndex "FIX085") then
	(if (and (>= ?iPlannedDate (daysAdd ?*easter* -7)) (<= ?iPlannedDate (daysAdd ?*easter* 7))) then
	    ;Set the date to the Monday after the Octave of Easter
	    (bind ?iNewDate (daysAdd ?*easter* 8))
	    (return ?iNewDate)
	)
    )
    
    ;Check the special arrangements for St Joseph's day
    ;  St Joseph's day, 19 March, is caught within Holy Week if Easter falls earlier than 27 March.
    (if (eq ?sTypeIndex "FIX079") then
	(if (< ?*easter* (mkDate ?*yearSought* 3 27)) then
	    (if (and (eq ?*calendarInUse* "AU") (< ?*easter* (mkDate ?*yearSought* 3 25))) then
		;Set the date to the Friday before Palm Sunday
		(bind ?iNewDate (daysAdd ?*easter* -9))
	    else
		;Set the date to the Saturday before Palm Sunday
		(bind ?iNewDate (daysAdd ?*easter* -8))
	    )
	    (return ?iNewDate)
	)
    )
    
    ;Check the special arrangements for St Patrick's day
    ;  Where St Patrick's Day is a solemnity and is caught within Holy Week,
    ;   St Joseph's Day is moved a day earlier.
    ;  St Patrick's is caught within Holy Week if Easter falls earlier than 25 March.
    ; List the TypeIndex from the appropriate Local Calendars here. (NB This CE could be improved.)
    (if (or (eq ?sTypeIndex "CAL_AU001") (eq ?sTypeIndex "CAL_IE002")) then
	(if (< ?*easter* (mkDate ?*yearSought* 3 25)) then
	    ;Set the date to the Saturday before Palm Sunday
	    (bind ?iNewDate (daysAdd ?*easter* -8))
	    (return ?iNewDate)
	)
    )
    
    ;Assume no general checking of solemnities occuring within Holy Week and the Easter Octave,
    ;   as the three chief examples have been handled above.
    
    ;Check against Sundays of Advent, Lent, and Easter
    (if (= (DoW ?iPlannedDate) 7) then
	;Check against Sundays of Advent
	(if (or (= ?iPlannedDate ?*firstSundayAdvent*) (= ?iPlannedDate (daysAdd ?*firstSundayAdvent* 7)) (= ?iPlannedDate (daysAdd ?*firstSundayAdvent* 14)) (= ?iPlannedDate (daysAdd ?*firstSundayAdvent* 21))) then
	    (bind ?iNewDate (daysAdd ?iPlannedDate 1))
	    (return ?iNewDate)
	)
	;Check against Sundays of Lent and Eastertide
	(if (and (>= ?iPlannedDate (daysAdd ?*easter* -42)) (<= ?iPlannedDate (daysAdd ?*easter* 49))) then
	    (bind ?iNewDate (daysAdd ?iPlannedDate 1))
	    (return ?iNewDate)
	)
    )
    
    ;Default value is the original calculated value passed into the function.
    ?iNewDate
)   
