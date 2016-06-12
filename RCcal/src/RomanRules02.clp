;####################################################################################
;;;;;; To print out the facts, use: (printout t (facts) crlf) .
;;;    To save facts to a file, use: (save-facts "harry.clp") .
;;;

(defrule clearGENforLocal
    (declare (salience ?*supreme-priority*))
    (phase-02-majorFeasts)
    (test (member$ calendarInUse (get-defglobal-list)))
    ?f1 <- (ReplacesInGenFact (TypeIndex ?f1TypeIndex&~nil)
                (CalType ?f1CalType&:(eq ?f1CalType ?*calendarInUse*))
                (ReplacesInGEN ?f1Replaces)
            )
    ?f2 <- (CalendarFact (CalType ?f2CalType&:(eq ?f2CalType ?*GENERAL_ROMAN_CALENDAR*))
                (TypeIndex ?f2TypeIndex&:(eq ?f1Replaces ?f2TypeIndex))
            )
    =>
    ;This fact presumes that the General and the local (i.e., particular) calendar facts have been loaded.
    ;  It then removes those facts in the general calendar that are identified as being replaced  by facts
    ;   in the local calendar. For example, the local celebration of St Patrick may raise it to a Solemnity,
    ;   so the Optional Memorial for St Patrick in the general calendar is effectively replaced.
    (retract ?f2)
)

;The highest ranking feasts. The digits after "p" in the name of the rule,
;  represent the position in the Table of Liturgical Days.
;  These rules could be written more effeciently, but are written this way so that
;  aligning them with the Table of Liturgical Days is easy. (Should there be updates in the future.)
(defrule p000CivilCommemorations
    ;For example, national holidays.
    ;These are called "Days of Prayer" in the Roman Missal.
    (declare (salience ?*highest-priority*))
    (phase-02-majorFeasts)
    (test (member$ calendarInUse (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (CalType ?fCalType&:(eq ?fCalType ?*calendarInUse*))
                         (Rank ?fRank&:(eq ?fRank "Civil Commemoration"))
                         (Day ?fDay)
                         (Month ?fMonth)
                         (TypeIndex ?fTypeIndex))
    =>
    ;These civil commemorations, such as national holidays, are not found in the General Roman Calendar.
    ;   Hence this rule only seeks civil commemorations from the local calendar.

    ;Special case of CAL_US004 ("Day of Prayer for the Legal Protection of Unborn Children")
    (if (and (eq ?*calendarInUse* "US") (eq ?fTypeIndex "CAL_US004")) then
        (bind ?iTemp (mkDate ?*yearSought* ?fMonth ?fDay))
        ;if 22 Jan falls on a Sunday, this Day of Prayer falls on 23 Jan.
        (if (= (DoW ?iTemp) 7) then
            ;we move the date to 23 Jan
            (bind ?iTemp (daysAdd ?iTemp 1))
            ;Create a ReplacesInGen fact, as we now know the date.
            (assert (ReplacesInGenFact (TypeIndex "CAL_US004") (CalType "US") (ReplacesInGEN "FIX023")))
        else
            ;date remains 22 Jan
            ;Create a ReplacesInGen fact, as we now know the date.
            (assert (ReplacesInGenFact (TypeIndex "CAL_US004") (CalType "US") (ReplacesInGEN "FIX022")))
        )
    else
        ;For all other cases, take the date given
        (if (and (neq ?fTypeIndex "CAL_US020") (neq ?fTypeIndex "CAL_AU015") (neq ?fTypeIndex "CAL_AU016")) then
            ;We treat US Thanksgiving and Australian Ember Days in a separate rule
            (bind ?iTemp (mkDate ?*yearSought* ?fMonth ?fDay))
        )
    )

    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1001)))
)
(defrule p000aCivilCommemorationUSThanksgiving
    ;Called "Days of Prayer" in the Roman Missal.
    (declare (salience ?*highest-priority*))
    (phase-02-majorFeasts)
    (test (member$ calendarInUse (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (CalType ?fCalType&:(eq ?fCalType ?*calendarInUse*))
                         (TypeIndex ?fTypeIndex&:(eq ?fTypeIndex "CAL_US020")))
    =>
    ;Fourth Thursday in November
    (bind ?iTemp (mkDate ?*yearSought* 11 1))
    (while (<> (DoW ?iTemp) 4)
        (bind ?iTemp (daysAdd ?iTemp 1))
    )
    ;We have found the first, now find the fourth
    (bind ?iTemp (daysAdd ?iTemp 21))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1001)))
)
(defrule p130bAllSaintsEnglandAndWales
    (declare (salience ?*highest-priority*))
    (phase-02-majorFeasts)
    (test (member$ calendarInUse (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?f1Date_this_year&nil)
                         (CalType ?f1CalType)
                         (Day ?f1Day)
                         (Month ?f1Month)
                         (TypeIndex ?f1TypeIndex&:(eq ?f1TypeIndex "FIX306")))
    ?f2 <- (CalendarFact (Date_this_year ?f2Date_this_year&nil)
                         (CalType ?f2CalType)
                         (TypeIndex ?f2TypeIndex&:(eq ?f2TypeIndex "FIX307")))
    ?f3 <- (CalendarFact (Date_this_year ?f3Date_this_year&nil)
                         (CalType ?f3CalType)
                         (TypeIndex ?f3TypeIndex&:(eq ?f3TypeIndex "FIX308")))
    ?f4 <- (CalendarFact (Date_this_year ?f4Date_this_year&nil)
                         (CalType ?f4CalType)
                         (TypeIndex ?f4TypeIndex&:(or (eq ?f4TypeIndex "CAL_ENG034") (eq ?f4TypeIndex "CAL_WLS017"))))
    =>
    ;In England and Wales, if All Saints falls on a Saturday, it and the subsequent All Souls are moved
    ;  one day forward, so that All Saints then falls on a Sunday. All Saints, generally 1 Nov, is FIX306.

    (if (or (eq ?*calendarInUse* "ENG") (eq ?*calendarInUse* "WLS")) then
        (bind ?iTemp (mkDate ?*yearSought* ?f1Month ?f1Day))
        (if (= (DoW ?iTemp) 6) then
            (bind ?iTemp (daysAdd ?iTemp 1))
            (modify ?f1 (Date_this_year ?iTemp))
            (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?f1TypeIndex) (TableLitDayRank 1303)))
            (bind ?iTemp (daysAdd ?iTemp 1))
            (modify ?f2 (Date_this_year ?iTemp))
            (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?f2TypeIndex) (TableLitDayRank 1303)))
            (retract ?f3)
            (retract ?f4)
        else
            ;Just asserting one will be sufficient to stop this rule firing again.
            (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?f1TypeIndex) (TableLitDayRank 1303)))
        )
    )
)
(defrule p000aCivilCommemorationAUEmberDay
    ;Called "Days of Prayer and Penance" in the Roman Missal.
    (declare (salience ?*highest-priority*))
    (phase-02-majorFeasts)
    (test (member$ calendarInUse (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (CalType ?fCalType&:(eq ?fCalType ?*calendarInUse*))
                         (TypeIndex ?fTypeIndex&:(or (eq ?fTypeIndex "CAL_AU015") (eq ?fTypeIndex "CAL_AU016"))))
    =>
    (if (eq ?fTypeIndex "CAL_AU015") then
        ;First Friday in March
        (bind ?iTemp (mkDate ?*yearSought* 3 1))
        (while (<> (DoW ?iTemp) 5)
            (bind ?iTemp (daysAdd ?iTemp 1))
        )
    )
    (if (eq ?fTypeIndex "CAL_AU016") then
        ;First Friday in September
        (bind ?iTemp (mkDate ?*yearSought* 9 1))
        (while (<> (DoW ?iTemp) 5)
            (bind ?iTemp (daysAdd ?iTemp 1))
        )
    )

    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1001)))
)
(defrule p110EasterTriduum
    (declare (salience ?*highest-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                            (DaysFromEaster ?fDaysFromEaster&~nil&:(and (< ?fDaysFromEaster 1) (>= ?fDaysFromEaster -3)))
                            (TypeIndex ?fTypeIndex))
    =>
    (if (not (integerp ?fDaysFromEaster)) then
        (bind ?iDays (string-to-integer ?fDaysFromEaster))
    else
        (bind ?iDays ?fDaysFromEaster)
    )
    (bind ?iTemp (daysAdd ?*easter* ?iDays))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1100)))
)
(defrule p121Christmas
    (declare (salience ?*highest-priority*))
    (phase-02-majorFeasts)
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (TypeIndex ?fTypeIndex&:(eq ?fTypeIndex "FIX360")))
    =>
    (bind ?iTemp (mkDate ?*yearSought* 12 25))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1210)))
)
(defrule p122aEpiphanyMovedToSunday
    (declare (salience ?*highest-priority*))
    (phase-02-majorFeasts)
    (test (member$ calendarInUse (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (TypeIndex ?fTypeIndex&:(eq ?fTypeIndex "VAR034"))
                         (EarliestMonth ?fEarliestMonth)
                         (EarliestDay ?fEarliestDay)
                         (LatestMonth ?fLatestMonth)
                         (LatestDay ?fLatestDay))
           (MovedToSundaysFact (TypeIndex ?f2TypeIndex&:(eq ?f2TypeIndex "VAR034"))
                           (CalType ?f2CalType&:(eq ?f2CalType ?*calendarInUse*)))
    =>
    (bind ?iTemp (clFindSun (mkDate ?*yearSought* ?fEarliestMonth ?fEarliestDay) (mkDate ?*yearSought* ?fLatestMonth ?fLatestDay)))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1220)))
)
(defrule p122bEpiphany
    (declare (salience ?*higher-priority*))
    (phase-02-majorFeasts)
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (TypeIndex ?fTypeIndex&:(eq ?fTypeIndex "VAR034")))
    =>
    (bind ?iTemp (mkDate ?*yearSought* 1 6))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1220)))
)
(defrule p123aAscensionMovedToSunday
    (declare (salience ?*highest-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    (test (member$ calendarInUse (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (TypeIndex ?fTypeIndex&:(eq ?fTypeIndex "VAR041")))
           (MovedToSundaysFact (TypeIndex ?f2TypeIndex&:(eq ?f2TypeIndex "VAR041"))
                           (CalType ?fCalType&:(eq ?fCalType ?*calendarInUse*)))
    =>
    (bind ?iTemp (daysAdd ?*easter* 42))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1230)))
)
(defrule p123bAscension
    (declare (salience ?*higher-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (TypeIndex ?fTypeIndex&:(eq ?fTypeIndex "VAR041")))
    =>
    (bind ?iTemp (daysAdd ?*easter* 39))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1230)))
)
(defrule p124Pentecost
    (declare (salience ?*highest-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (TypeIndex ?fTypeIndex&:(eq ?fTypeIndex "MOV096")))
    =>
    (bind ?iTemp (daysAdd ?*easter* 49))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1240)))
)
(defrule p125SundaysOfAdvent
    (declare (salience ?*higher-priority*))
    (phase-02-majorFeasts)
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
               (TypeIndex ?fTypeIndex&:(or (eq ?fTypeIndex "VAR003") (eq ?fTypeIndex "VAR010") (eq ?fTypeIndex "VAR017") (eq ?fTypeIndex "VAR024"))))
    =>
    ;The First Sunday of Advent has a TypeIndex of "VAR003", and the series continues contiguously
    ;   until the Fourth Sunday of Advent, which is "VAR024".
    (bind ?iTemp (string-to-integer (sub-string 4 6 ?fTypeIndex)))
    (bind ?iTemp (daysAdd ?*firstSundayAdvent* (- ?iTemp 3)))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1250)))
)
(defrule p126SundaysOfLent
    (declare (salience ?*higher-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
               (DaysFromEaster ?fDaysFromEaster&~nil)
               (TypeIndex ?fTypeIndex&:(or (eq ?fTypeIndex "MOV005") (eq ?fTypeIndex "MOV012") (eq ?fTypeIndex "MOV019") (eq ?fTypeIndex "MOV026") (eq ?fTypeIndex "MOV033") (eq ?fTypeIndex "MOV040"))))
    =>
    (if (not (integerp ?fDaysFromEaster)) then
        (bind ?iDays (string-to-integer ?fDaysFromEaster))
    else
        (bind ?iDays ?fDaysFromEaster)
    )
    (bind ?iTemp (daysAdd ?*easter* ?iDays))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1260)))
)
(defrule p127SundaysOfEastertide
    (declare (salience ?*higher-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
               (DaysFromEaster ?fDaysFromEaster&~nil)
               (TypeIndex ?fTypeIndex&:(or (eq ?fTypeIndex "MOV054") (eq ?fTypeIndex "MOV061") (eq ?fTypeIndex "MOV068") (eq ?fTypeIndex "MOV075") (eq ?fTypeIndex "MOV082") (eq ?fTypeIndex "MOV089"))))
    =>
    (if (not (integerp ?fDaysFromEaster)) then
        (bind ?iDays (string-to-integer ?fDaysFromEaster))
    else
        (bind ?iDays ?fDaysFromEaster)
    )
    (bind ?iTemp (daysAdd ?*easter* ?iDays))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1270)))
)
(defrule p128AshWednesday
    (declare (salience ?*higher-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
               (DaysFromEaster ?fDaysFromEaster&~nil)
               (TypeIndex ?fTypeIndex&:(eq ?fTypeIndex "MOV001")))
    =>
    (if (not (integerp ?fDaysFromEaster)) then
        (bind ?iDays (string-to-integer ?fDaysFromEaster))
    else
        (bind ?iDays ?fDaysFromEaster)
    )
    (bind ?iTemp (daysAdd ?*easter* ?iDays))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1280)))
)
(defrule p129HolyAndEasterWeeks
    (declare (salience ?*higher-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
               (DaysFromEaster ?fDaysFromEaster&~nil)
               (TypeIndex ?fTypeIndex&:(>= (str-compare ?fTypeIndex "MOV041") 0)&:(<= (str-compare ?fTypeIndex "MOV053") 0)))
    =>
    (if (not (integerp ?fDaysFromEaster)) then
        (bind ?iDays (string-to-integer ?fDaysFromEaster))
    else
        (bind ?iDays ?fDaysFromEaster)
    )
    (bind ?iTemp (daysAdd ?*easter* ?iDays))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1290)))
)
(defrule p130aCorpusChristiToSunday
    (declare (salience ?*highest-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    (test (member$ calendarInUse (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (TypeIndex ?fTypeIndex&:(eq ?fTypeIndex "VAR042")))
    ?f4 <- (CalendarFact (Date_this_year ?f4Date_this_year)
                         (TypeIndex ?f4TypeIndex&:(eq ?f4TypeIndex "MOV110"))
                         (DaysFromEaster ?f4DaysFromEaster))
           (MovedToSundaysFact (TypeIndex ?f2TypeIndex&:(eq ?f2TypeIndex "VAR042"))
                           (CalType ?f2CalType&:(eq ?f2CalType ?*calendarInUse*)))
    =>
    (if (not (integerp ?f4DaysFromEaster)) then
        (bind ?iDays (string-to-integer ?f4DaysFromEaster))
    else
        (bind ?iDays ?f4DaysFromEaster)
    )
    (bind ?iTemp (daysAdd ?*easter* ?iDays))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1300)))
    (retract ?f4)
)
(defrule p130bCorpusChristi
    (declare (salience ?*high-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (TypeIndex ?fTypeIndex&:(eq ?fTypeIndex "VAR042")))
    ?f3 <- (CalendarFact (Date_this_year ?f3Date_this_year)
                         (TypeIndex ?f3TypeIndex&:(eq ?f3TypeIndex "MOV107"))
                         (DaysFromEaster ?f3DaysFromEaster))
    =>
    (if (not (integerp ?f3DaysFromEaster)) then
        (bind ?iDays (string-to-integer ?f3DaysFromEaster))
    else
        (bind ?iDays ?f3DaysFromEaster)
    )
    (bind ?iTemp (daysAdd ?*easter* ?iDays))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1300)))
    (retract ?f3)
)
(defrule p262dChristTheKing
    (declare (salience ?*high-priority*))
    (phase-02-majorFeasts)
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (TypeIndex ?f1TypeIndex&:(eq ?f1TypeIndex "VAR002")))
    ?f2 <- (CalendarFact (Date_this_year ?f2Date_this_year&~nil)
                         (TypeIndex ?f2TypeIndex&:(eq ?f2TypeIndex "VAR003")))
    =>
    (bind ?iTemp (daysAdd ?f2Date_this_year -7))
    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?f1TypeIndex) (TableLitDayRank 1300)))
)
(defrule p130cGeneralSolemnities
    (declare (salience ?*med-high-priority*))
    (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (CalType ?fCalType&:(eq ?fCalType ?*GENERAL_ROMAN_CALENDAR*))
                         (Rank ?fRank&:(eq ?fRank "Solemnity"))
                         (Day ?fDay)
                         (Month ?fMonth)
                         (DaysFromEaster ?fDaysFromEaster)
                         (TypeIndex ?fTypeIndex))
    =>
    (if (neq ?fDaysFromEaster nil) then
        (if (not (integerp ?fDaysFromEaster)) then
            (bind ?iDays (string-to-integer ?fDaysFromEaster))
        else
            (bind ?iDays ?fDaysFromEaster)
        )
        (bind ?iTemp (daysAdd ?*easter* ?iDays))
    else
        (bind ?iTemp (mkDate ?*yearSought* ?fMonth ?fDay))
    )
    ;Check whether the date for the Solemnity is valid, or should be moved.
    (bind ?iTemp (pFindNewDateForSolemnities ?iTemp ?fTypeIndex))

    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1303)))
)
(defrule p140LocalSolemnities
    (declare (salience ?*med-high-priority*))
    ?f2 <- (phase-02-majorFeasts)
    (test (member$ easter (get-defglobal-list)))
    (test (member$ calendarInUse (get-defglobal-list)))
    ?f1 <- (CalendarFact (Date_this_year ?fDate_this_year&nil)
                         (CalType ?fCalType&:(eq ?fCalType ?*calendarInUse*))
                         (Rank ?fRank&:(eq ?fRank "Solemnity"))
                         (Day ?fDay)
                         (Month ?fMonth)
                         (DaysFromEaster ?fDaysFromEaster)
                         (TypeIndex ?fTypeIndex))
    =>
    (if (neq ?fDaysFromEaster nil) then
        (if (not (integerp ?fDaysFromEaster)) then
            (bind ?iDays (string-to-integer ?fDaysFromEaster))
        else
            (bind ?iDays ?fDaysFromEaster)
        )
        (bind ?iTemp (daysAdd ?*easter* ?iDays))
    else
        (bind ?iTemp (mkDate ?*yearSought* ?fMonth ?fDay))
    )
    ;Check whether the date for the Solemnity is valid, or should be moved.
    (bind ?iTemp (pFindNewDateForSolemnities ?iTemp ?fTypeIndex))

    (modify ?f1 (Date_this_year ?iTemp))
    (assert (RCcalThisYear (Date_this_year ?iTemp) (TypeIndex ?fTypeIndex) (TableLitDayRank 1400)))
)
(defrule beginPhaseThree
    (declare (salience ?*lowest-priority*))
    ?f1 <- (phase-02-majorFeasts)
    =>
    ;Due to salience, this should run after all the Phase 02 rules have fired.
    (assert (phase-03-minorFeasts))
    (assert (do-YearlyReadingsCycle))
    (retract ?f1)
)
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
;;; The following fact should run during all phases beginning with Phase 02.
;;; It sorts out which of several feasts should be celebrated on a given day, if there is more than one
;;;  on that date. Salience affects the order, but we cannot test for the existence of a prior fact
;;;  on a given date, because at the left-hand part of the rule, we do not yet know the date.
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
(defrule aAvoidCollisions
    (declare (salience ?*highest-priority*))
    ?f1 <- (RCcalThisYear (Date_this_year ?f1Date_this_year&~nil)
                    (TypeIndex ?f1TypeIndex)
                    (TableLitDayRank ?f1TableLitDayRank&~nil)
                    (Optional1 ?f1Optional1)
                    (Optional2 ?f1Optional2)
                    (Optional3 ?f1Optional3)
                    (CurrentCycle ?f1CurrentCycle)
            )
    ?c1 <- (CalendarFact (Date_this_year ?f1Date_this_year&~nil)
                    (TypeIndex ?c1TypeIndex&:(eq ?c1TypeIndex ?f1TypeIndex))
                    (Rank ?c1Rank)
            )
    ?f2 <- (RCcalThisYear (Date_this_year ?f2Date_this_year&~nil&:(eq ?f1Date_this_year ?f2Date_this_year))
                    (TypeIndex ?f2TypeIndex&:(neq ?f1TypeIndex ?f2TypeIndex))
                    (TableLitDayRank ?f2TableLitDayRank&~nil)
                    (Optional1 ?f2Optional1)
                    (Optional2 ?f2Optional2)
                    (Optional3 ?f2Optional3)
                    (CurrentCycle ?f2CurrentCycle)
            )
    ?c2 <- (CalendarFact (Date_this_year ?f2Date_this_year&~nil)
                    (TypeIndex ?c2TypeIndex&:(eq ?c2TypeIndex ?f2TypeIndex))
                    (Rank ?c2Rank)
            )
    =>
    ;Get the Ranking so we can compare
    (if (not (integerp ?f1TableLitDayRank)) then
        (bind ?iRank1 (string-to-integer ?f1TableLitDayRank))
    else
        (bind ?iRank1 ?f1TableLitDayRank)
    )
    (if (not (integerp ?f2TableLitDayRank)) then
        (bind ?iRank2 (string-to-integer ?f2TableLitDayRank))
    else
        (bind ?iRank2 ?f2TableLitDayRank)
    )

    ;Check if within a cycle
    (if (or (eq (sub-string 1 3 ?f1TypeIndex) "ORW") (and (eq (sub-string 1 3 ?f1TypeIndex) "MOV") (< (str-compare ?f1TypeIndex "MOV097") 0))) then
        (bind ?sCycle ?f1TypeIndex)
    else
        (if (or (eq (sub-string 1 3 ?f2TypeIndex) "ORW") (and (eq (sub-string 1 3 ?f2TypeIndex) "MOV") (< (str-compare ?f2TypeIndex "MOV097") 0))) then
            (bind ?sCycle ?f2TypeIndex)
        else
            (bind ?sCycle nil)
        )
    )

    ;If the rankings are not equal, we should delete the lesser in importance (higher TableLitDayRank) and proceed.
    ;Retract the RCcalThisYear fact for the lesser ranking feast.
    (if (< ?iRank1 ?iRank2) then
        (if (and (eq ?f1CurrentCycle nil) (neq ?f2CurrentCycle nil)) then
            (modify ?f1 (CurrentCycle ?f2TypeIndex))
        )
        (retract ?f2)
    else
        (if (< ?iRank2 ?iRank1) then
            (if (and (eq ?f2CurrentCycle nil) (neq ?f1CurrentCycle nil)) then
                (modify ?f2 (CurrentCycle ?f1TypeIndex))
            )
            (retract ?f1)
            ;;;The missing else would cater where ?iRank1 = ?iRank2. This is found in the next If-statement.
        )
    )

    ;Check to see whether feasts are equal; mostly the case with Optional Memorias. Only one Memoria may
    ;   coincide.
    ; Here we assume the fact with "FIX" supersedes the others.
    (if (= ?iRank1 ?iRank2) then
        (if (and (neq ?c1Rank "Optional Memoria") (neq ?c1Rank "Memoria")) then
            ;We have a collision of Feasts or Solemnities, which should never occur.
            ;  These collisions should have been handled in the appropriate rule by celebration type.
            ;  We can thus not determine the more important feast, and merely suppress the second.
            (retract ?f2)
        else
            (if (or (and (eq ?c1Rank "Memoria") (eq ?f2TypeIndex "MOV116")) (and (eq ?f1TypeIndex "MOV116") (eq ?c2Rank "Memoria"))) then
                ;Used to be: (and (or (eq ?c1Rank "Memoria") (eq ?c2Rank "Memoria")) (or (eq ?f1TypeIndex "MOV116") (eq ?f2TypeIndex "MOV116"))) .
                ;The one occasion where a Memoria clashes with another one.
                (if (neq ?sCycle nil) then
                    (if (eq ?f1Optional1 nil) then
                        (modify ?f1 (TypeIndex ?sCycle) (Optional1 ?f1TypeIndex) (Optional2 ?f2TypeIndex))
                    else
                        (if (eq ?f1Optional2 nil) then
                            (modify ?f1 (TypeIndex ?sCycle) (Optional2 ?f1TypeIndex) (Optional3 ?f2TypeIndex))
                        )
                    )
                else
                    (if (eq ?f1Optional1 nil) then
                        (modify ?f1 (Optional1 ?f2TypeIndex))
                    else
                        (if (eq ?f1Optional2 nil) then
                            (modify ?f1 (Optional2 ?f2TypeIndex))
                        else
                            (modify ?f1 (Optional3 ?f2TypeIndex))
                        )
                    )
                )
                (retract ?f2)
            else
                ;Clashing Optional Memorias.
                (if (eq (sub-string 1 3 ?f1TypeIndex) "FIX") then
                    (if (eq ?f1Optional1 nil) then
                        (modify ?f1 (Optional1 ?f2TypeIndex) (Optional2 ?f2Optional1) (Optional3 ?f2Optional2) (CurrentCycle ?sCycle))
                        (retract ?f2)
                    else
                        (if (eq ?f1Optional2 nil) then
                            (modify ?f1 (Optional2 ?f2TypeIndex) (Optional3 ?f2Optional1) (CurrentCycle ?sCycle))
                            (retract ?f2)
                        else
                            (if (eq ?f1Optional2 nil) then
                                (modify ?f1 (Optional3 ?f2TypeIndex) (CurrentCycle ?sCycle))
                                (retract ?f2)
                            )
                        )
                    )
                else
                    ;Not "FIX"
                    (if (neq ?sCycle nil) then
                        (if (eq ?f1Optional1 nil) then
                            (modify ?f1 (TypeIndex ?sCycle) (Optional1 ?f1TypeIndex) (Optional2 ?f2TypeIndex) (Optional3 ?f2Optional1))
                            (retract ?f2)
                        else
                            (modify ?f1 (TypeIndex ?sCycle) (Optional2 ?f1TypeIndex) (Optional3 ?f2TypeIndex))
                            (retract ?f2)
                        )
                    else
                        (if (eq ?f1Optional1 nil) then
                            (modify ?f1 (Optional1 ?f2TypeIndex) (Optional2 ?f2Optional1) (Optional3 ?f2Optional2)(CurrentCycle ?sCycle))
                            (retract ?f2)

                        else
                            (if (eq ?f1Optional2 nil) then
                                (modify ?f1 (Optional2 ?f2TypeIndex) (Optional3 ?f2Optional1) (CurrentCycle ?sCycle))
                                (retract ?f2)
                            else
                                (if (eq ?f1Optional3 nil) then
                                    (modify ?f1 (Optional3 ?f2TypeIndex) (CurrentCycle ?sCycle))
                                    (retract ?f2)
                                )
                            )
                        )
                    )
                )
            )
        )
    )
)
