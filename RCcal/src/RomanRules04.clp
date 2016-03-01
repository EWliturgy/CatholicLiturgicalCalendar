;####################################################################################
;;;;;; To print out the facts, use: (printout t (facts) crlf) .
;;;    To save facts to a file, use: (save-facts "harry.clp") .
;;;     (do-for-all-facts ((?m RCcalThisYear)) (neq ?m:Date_this_year nil) (printout t (unmakeDate ?m:Date_this_year) " : " ?m:TypeIndex crlf))

;;; These rules come from the GILH. They presume that the rules from earlier phases have fired
;;;  and have created the necessary facts.
(defrule pSolemnityTexts
    (declare (salience ?*high-priority*))
    (phase-04-officeTexts)
    ?f1 <- (RCcalThisYear (Date_this_year ?f1Date_this_year&~nil)
                    (TypeIndex ?f1TypeIndex)
                    (CurrentCycle ?f1CurrentCycle&nil)
           )
    ?f2 <- (CalendarFact (Date_this_year ?f2Date_this_year&~nil)
                    (TypeIndex ?f2TypeIndex&:(eq ?f2TypeIndex ?f1TypeIndex))
                    (Rank ?f2Rank)
                    (Lit_rank ?f2Lit_rank)
            )
    =>
    (assert (DailyLiturgyDetails))
)
