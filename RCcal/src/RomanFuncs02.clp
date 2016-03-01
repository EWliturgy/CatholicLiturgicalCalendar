;####################################################################################
;;;;;; To print out the facts use: (printout t (facts) crlf) .

;;;These functions calculate the date of Easter, including the Easter falling before or after a given date.

;;;Rules and functions start here
(deffunction F10_CalcEaster
    (?imYear ?imMethod)
    
    ;validate the arguments
    (if (or (< ?imMethod ?*iEDM_JULIAN*) (> ?imMethod ?*iEDM_WESTERN*)) then
        (return nil)
    )
    (if (and (= ?imMethod ?*iEDM_JULIAN*) (< ?imYear ?*iFIRST_EASTER_YEAR*)) then
        (return nil)
    )
    (if (and (or (= ?imMethod ?*iEDM_ORTHODOX*) (= ?imMethod ?*iEDM_WESTERN*)) (or (< ?imYear ?*iFIRST_VALID_GREGORIAN_YEAR*) (> ?imYear ?*iLAST_VALID_GREGORIAN_YEAR*))) then
        (return nil)
    )

    ;Using the formula by Jean Meeus in his book Astronomical Algorithms (1991, p. 69)
    (if (or (= ?imMethod ?*iEDM_JULIAN*) (= ?imMethod ?*iEDM_ORTHODOX*)) then
        (bind ?iA (mod ?imYear 4))
        (bind ?iB (mod ?imYear 7))
        (bind ?iC (mod ?imYear 19))
        (bind ?iD (mod (+ (* 19 ?iC) 15) 30))
        (bind ?iE (mod (+ (- (+ (* 2 ?iA) (* 4 ?iB)) ?iD) 34) 7))
        (bind ?iTemp (+ ?iD ?iE 114))
        (bind ?iF (div ?iTemp 31))
        (bind ?iG (mod ?iTemp 31))
        (bind ?iMonth ?iF)
        (bind ?iDay (+ ?iG 1))
        (bind ?dTemp (mkDate ?imYear ?iMonth ?iDay))
        (if (= ?imMethod ?*iEDM_ORTHODOX*) then
            ;(bind ?iTemp (pJulianToCJDN ?imYear ?iMonth ?iDay))
            ;(bind ?dTemp (pCJDNToMilankovic ?iTemp))
            (bind ?iTemp (daysAdd ?dTemp (CalcDayDiffJulianCal ?dTemp)))
            (return ?iTemp)
        else
            ;return Julian date for Easter
            (return ?dTemp)
        )
    else
        (if (= ?imMethod ?*iEDM_WESTERN*) then
             ;From Ian Stewarts page of OBeirnes formula:
            ;   http://www.whydomath.org/Reading_Room_Material/ian_stewart/2000_03.html .
            (bind ?iA (mod ?imYear 19))
            ;;;   ?iA + 1 is the year's Golden Number.
            (bind ?iB (div ?imYear 100))
            (bind ?iC (mod ?imYear 100))
            (bind ?iD (div ?iB 4))
            (bind ?iE (mod ?iB 4))
            (bind ?iG (div (+ (* 8 ?iB) 13) 25))
            (bind ?iH (mod (+ (- (- (+ (* 19 ?iA) ?iB) ?iD) ?iG) 15) 30))
            ;;;   The year's Epact is 23 – ?iH when ?iH is less than 24 and 53 – ?iH otherwise.
            (bind ?iM (div (+ ?iA (* 11 ?iH)) 319))
            (bind ?iJ (div ?iC 4))
            (bind ?iK (mod ?iC 4))
            (bind ?iL (mod (+ (+ (- (- (+ (* 2 ?iE) (* 2 ?iJ)) ?iK) ?iH) ?iM) 32) 7))
            (bind ?iN (div (+ (+ (- ?iH ?iM) ?iL) 90) 25))
            (bind ?iP (mod (+ (+ (+ (- ?iH ?iM) ?iL) ?iN) 19) 32))
            ;;;   The years dominical letter can be found by dividing 2E + 2J – K by 7,
            ;;;      and taking the remainder (a remainder of 0 is equivalent to the letter A,
            ;;;      1 is equivalent to B, and so on.
            (bind ?imDay ?iP)
            (bind ?imMonth ?iN)
            (bind ?dTemp (mkDate ?imYear ?imMonth ?imDay))
            (return ?dTemp)
        else
            (return nil)
        )
    )

)

(deffunction F09_CalcPreviousEaster
    (?dDate ?iDateMethod)
    
    ;Check arguments
    (bind ?iYearTemp (yearFromDateINT ?dDate))
    (if (or (not (integerp ?dDate)) (not (integerp ?iDateMethod))) then
        (return nil)
    )
    (if (or (< ?iDateMethod ?*iEDM_JULIAN*) (> ?iDateMethod ?*iEDM_WESTERN*)) then
        (return nil)
    )
    
    (bind ?dDateHolder (F10_CalcEaster ?iYearTemp ?iDateMethod))
    (if (< ?dDateHolder ?dDate) then
        (return ?dDateHolder)
    else
        (return (F10_CalcEaster (- ?iYearTemp 1) ?iDateMethod))
    )
)

(deffunction F11_CalcNextEaster
    (?dDate ?iDateMethod)
    
    
    ;Check arguments
    (bind ?iYearTemp (yearFromDateINT ?dDate))
    (if (or (not (integerp ?dDate)) (not (integerp ?iDateMethod))) then
        (return nil)
    )
    (if (or (< ?iDateMethod ?*iEDM_JULIAN*) (> ?iDateMethod ?*iEDM_WESTERN*)) then
        (return nil)
    )
    
    (bind ?dDateHolder (F10_CalcEaster ?iYearTemp ?iDateMethod))
    (if (> ?dDateHolder ?dDate) then
        (return ?dDateHolder)
    else
        (return (F10_CalcEaster (+ ?iYearTemp 1) ?iDateMethod))
    )
)
