;####################################################################################
;;;;;; To print out the facts use: (printout t (facts) crlf) .

;;;These rules just check that fundamental global variables are initialised.
;;  They must be initialised before any other rules are fires, so they thus represent Phase 1 of the process.

(defrule kickOffControlFactsForPhases
    (declare (salience ?*supreme-priority*))
    (not (phase-01-globals))
    (not (phase-02-majorFeasts))
    (not (phase-03-minorFeasts))
    (not (phase-04-officeTexts))
    =>
    ;This process presumes that rules that retract control facts, will have asserted the next control fact,
    ;   before retracting the current one.
    (assert (phase-01-globals))
)
(defrule checkEasterDatingMethod
    (declare (salience ?*highest-priority*))
    (phase-01-globals)
    (test (not (member$ EDM (get-defglobal-list))))
    =>
    ;If it hasn't been set, set it to a reasonable default; i.e., Western Easter (Gregorian Calendar).
    (build "(defglobal ?*EDM* = ?*iEDM_WESTERN*)")
)
(defrule checkEasterDatingMethodNotNULL
    (declare (salience ?*highest-priority*))
    (phase-01-globals)
    (test (and (member$ EDM (get-defglobal-list)) (eq (eval (sym-cat "?*" "EDM" "*")) nil)))
    
    =>
    ;If it hasn't been set, set it to a reasonable default; i.e., Western Easter (Gregorian Calendar).
    (build "(defglobal ?*EDM* = ?*iEDM_WESTERN*)")
)
(defrule checkTheYearInQuestion
    (declare (salience ?*highest-priority*))
    (phase-01-globals)
    (test (not (member$ yearSought (get-defglobal-list))))
    =>
    ;CLIPS cannot determine the system date, so this is a reasonable assumption.
    (build "(defglobal ?*yearSought* = 2015)")
)
(defrule checkTheYearInQuestionNotNULL
    (declare (salience ?*highest-priority*))
    (phase-01-globals)
    (test (and (member$ yearSought (get-defglobal-list)) (eq (eval (sym-cat "?*" "yearSought" "*")) nil)))
    =>
    ;In case we had to initialise the run with a nil value.
    ;CLIPS cannot determine the system date, so this is a reasonable assumption.
    (build "(defglobal ?*yearSought* = 2015)")
)
(defrule checkEaster
    (declare (salience ?*higher-priority*))
    (phase-01-globals)
    (test (not (member$ easter (get-defglobal-list))))
    =>
    (build "(defglobal ?*easter* = (F10_CalcEaster ?*yearSought* ?*EDM*))")
)
(defrule checkEasterNotNULL
    (declare (salience ?*higher-priority*))
    (phase-01-globals)
    (test (and (member$ easter (get-defglobal-list)) (eq (eval (sym-cat "?*" "easter" "*")) nil)))
    =>
    (build "(defglobal ?*easter* = (F10_CalcEaster ?*yearSought* ?*EDM*))")
)
(defrule firstSundayOfAdvent
    (declare (salience ?*high-priority*))
    (phase-01-globals)
    (test (not (member$ firstSundayAdvent (get-defglobal-list))))
    =>
    ;Find the fourth and final Sunday of Advent, 
    ;   then find the first Sunday of Advent.
    (build "(defglobal ?*firstSundayAdvent* = (daysAdd (clFindSun (mkDate ?*yearSought* 12 18) (mkDate ?*yearSought* 12 24)) -21))")
)
(defrule checkCalendarInUse
	(declare (salience ?*higher-priority*))
        (phase-01-globals)
	(test (not (member$ calendarInUse (get-defglobal-list))))
	=>
        ;If no local calendar has been selected, set its value to nil.
	(build "(defglobal ?*calendarInUse* = nil)")
)
(defrule firstSunOrdinaryTime
    (declare (salience ?*high-priority*))
    (phase-01-globals)
    (test (not (member$ FirstSunOrdinaryTime (get-defglobal-list))))
    =>
    (build "(defglobal ?*FirstSunOrdinaryTime* = nil)")
)
(defrule beginPhaseTwo
    (declare (salience ?*lowest-priority*))
    ?f1 <- (phase-01-globals)
    =>
    ;Due to salience, this should run after all the Phase 01 rules have fired.
    ;  We can now also remove the initial rule for control facts from the system.
    (undefrule kickOffControlFactsForPhases)
    (assert (phase-02-majorFeasts))
    (retract ?f1)
)
