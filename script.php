<script>
    console.clear();
    /* Set 5 as Initial Value on page load (index.php) */
    /**
     * 
     * @@@@@@@@@@@@@@@@@ DRAWING TOOL [By Mouse] @@@@@@@@@@@@@@@@@@
     * 
     */
    for (let i = 0; i < morder; i++) {
        XYPoints[i] = [];

        for (let j = 0; j < morder; j++) {
            XYPoints[i][j] = [i + 1, j + 1, false];
        }
    }

    /* ============================================================  */
    /* ############################# GET THE MOUSE DOWN POSITION ################ */
    /* ============================================================  */

    function getMousePosition(canvas, event) {
        let x = event.offsetX;
        let y = event.offsetY;
        let mouseClick = event.button;
        if (mouseClick === 1 || mouseClick === 0) {
            return [x, y];
        } else {
            return [0, 0];
        }
    } /* Mouse Position for an event */

    // the canvas is initialized
    if (typeof(poset) !== undefined) {

        /* For Hover Effect */
        function LeastDistanceShow(xCurrent, yCurrent) {
            let Start = -1,
                End = -1;
            let X1, Y1, X2, Y2;
            let minDistance = Number.MAX_SAFE_INTEGER,
                DistanceFromPointToLine = Number.MAX_SAFE_INTEGER,
                DistanceFromXY1ToXY2 = Number.MAX_SAFE_INTEGER;

            /* X Coordinate of Clicked Position */
            let xCCo = Math.floor(xCurrent / 50);

            /* selected Elements below xCurrent */
            let count = 0;
            coveringMatrix.forEach((element) => {
                if (element[0] <= xCCo) {
                    count++;
                }
            });

            /* Get Two Points From SelectedElements To Relate */
            for (let i = count - 1; i >= 0; i--) {
                /* Set X1, Y1 as 1st point down xCurrent */
                X1 = coveringMatrix[i][0] * x;
                Y1 = coveringMatrix[i][1] * x;

                /* Find Suitable X2, Y2 for 2nd point in up-xCurrent */
                for (let j = count; j < SELength; j++) {
                    if (coveringMatrix[j][0] * x > X1) {

                        /* take End point */
                        X2 = coveringMatrix[j][0] * x;
                        Y2 = coveringMatrix[j][1] * x;

                        /* Disntance From Start to End */
                        DistanceFromXY1ToXY2 = Math.sqrt(
                            Math.pow(X1 - X2, 2) + Math.pow(Y1 - Y2, 2)
                        );

                        /* Distance From Hovered Point To Start-End Line */
                        DistanceFromPointToLine =
                            Math.abs(
                                (Y2 - Y1) * xCurrent -
                                (X2 - X1) * yCurrent +
                                (Y1 * (X2 - X1) - X1 * (Y2 - Y1))
                            ) / DistanceFromXY1ToXY2;

                        /* Find The line of Nearest Two Elements (Least Distanced) in "radius" distance */
                        if (
                            DistanceFromXY1ToXY2 < minDistance &&
                            DistanceFromPointToLine <= radius
                        ) {
                            /* =========================================== */
                            /* Check! is Trivial Case [Cover of Sub-cover is also a cover] */
                            /* =========================================== */

                            var isCovered = (IsConnectable(i, j) === true) ? true : false;

                            if (coveringMatrix[i][2]?.includes(j)) {
                                var foundEnd = coveringMatrix[i][2].indexOf(j);
                            } else {
                                var foundEnd = -1;
                            }

                            if (isCovered || foundEnd !== -1) {
                                /* Update Distance but not Start & End. Becouse they directly connected or transitively Covered. */
                                minDistance = DistanceFromXY1ToXY2;
                                // document.getElementById("ShowErrors").hidden;
                            } else {
                                /* Save Smallest Path & Update In Every Possible Path */
                                minDistance = DistanceFromXY1ToXY2;

                                /* Save The Indices of X1, Y1 and X2, Y2 to return to finally drawn */
                                (Start = i), (End = j);
                            } // The Relation is Trivial

                        } // Click near the connection line or on nearest points // check is minDistanced
                    } // Relate only in upword direction
                } // For loop of j -> SELength Closed
            } // For loop of i -> 0 Closed
            return [Start, End];
        } // LeastDistanceShow()


        /* ============================================================  */
        /* ############################# Draw Solid Line If Click In Possible Connection ############################# */
        /* ============================================================  */
        function DrawPoset(xCurrent, yCurrent) {
            let K = 0;
            /* Find The Distance From The Mesh Points To The Clicked Point */
            [xCoord, yCoord, K] = findDistFromGrids(xCurrent, yCurrent);
            /* FILL IF NOT CHECKED UNFILLED IF CHECKED */

            if (DistanceFromGrids[K] <= radius + 1) {
                /* Fill The Element As Selected Nodes */
                fillOrUnFill(xCoord, yCoord);
            } else if (SELength > 1) {

                /* Connect Two Points If minimum TWO Elements Selected. */
                let [Start, End] = LeastDistance(xCurrent, yCurrent);

                /* If Found Any Nearest Selected Points To Connect */
                if (Start != -1) {
                    // console.log("Connect The Points: ", Start, End);
                    let connected = -1; // Let They are not connected.
                    /* Define array's new dimension for connectedness if not defined */
                    connectLine(Start, End);
                }
            } else {
                document.getElementById("ShowErrors").hidden = false;
                document.getElementById("ShowErrors").innerHTML =
                    "Please Select At Least Two Points Before To Connect.";
            }
        }

        /* ============================================================  */
        /* transitive-Closer property apply to the Matrix  */
        /* ============================================================  */
        function transitiveCloserMatrix() {
            SELength = coveringMatrix.length;
            if (SELength) {
                /* Length of the Upper Triangular Poset */
                let UTPosetSize = (Math.pow(SELength, 2) - SELength) / 2;

                /* transitive Closer Upper Triangular Matrix */
                UTPoset[0] = new Array(UTPosetSize).fill(0);

                /* to separate vars location in 2D Array copy */
                var UTCoverM = JSON.parse(JSON.stringify(coveringMatrix));

                /* check for every elements covering elements */
                for (let j = morder - 3; j >= 0; j--) {
                    // console.log('for each ', j, 'th element.');
                    if (
                        UTCoverM[j][2]?.[0] != undefined
                    ) {
                        UTCoverM[j][2].forEach(element => {
                            // console.log(j);
                            if (UTCoverM[element][2]?.[0] != undefined) {
                                UTCoverM[element][2].forEach(element1 => {
                                    if ((UTCoverM[j][2].findIndex(theElement => theElement === element1)) === -1) {
                                        UTCoverM[j][2].push(element1);
                                        UTCoverM[j][2].sort(Sorting2DArray);
                                    }
                                });
                            }
                        });
                    } /* UTCoverM's j'th element has no cover matrix */
                } /* END FOR loop :=> Transitive Cover Matrix */

                /* Find The Cover Matrix */
                let relations = [];
                for (let n_th_element = 0; n_th_element < SELength; n_th_element++) {
                    if (
                        UTCoverM[n_th_element][2]?.[0] != undefined
                    ) {
                        let RL = UTCoverM[n_th_element][2].length;
                        for (let n_th_relation = 0; n_th_relation < RL; n_th_relation++) {
                            let cover = UTCoverM[n_th_element][2][n_th_relation];
                            let idx = SELength * n_th_element + cover - ((n_th_element + 1) * (n_th_element + 2)) / 2; // Find the idx from relational values.
                            UTPoset[0][idx] = 1; // If related assign 1
                        }

                        /* Save The Number of Covers Elements */
                        relations[n_th_element] = coveringMatrix[n_th_element][2].length;

                    } else {
                        /* If no relations exist then no. is 0 */
                        relations[n_th_element] = 0;
                    }
                }
                return UTPoset[0];
            } /* IF END */
        } /* createRelationalTable() END */

        /* For isomorphicMatrices() */
        function PRIndexGrouping(arr) {
            if (arr.length === 0) {
                return [
                    []
                ]; // return an array with the empty subset
            }

            let last = arr.pop(),
                subsets = PRIndexGrouping(arr),
                newSubsets = subsets.map(subset => [...subset, last]); // create new subsets by adding the last element to each subset

            return [...subsets, ...newSubsets]; // combine the old and new subsets
        }
        /**
         * Get The Poset Line 
         * Make the Poset Matrix 
         * Count The Height Of each i [Elements/nodes] 
         * swap the rows & columns such that each i of same H remain together
         * [Find the indices i for which A[i][i+1] == 0 & H of i & i+1 are same]
         * Find The Power set of same H of i's & The Permutation of the Power set 
         *   */
        /* find the matrices which isomorphic to the given matrix 'coveringMatrix' */
        function isomorphicMatrices() {
            // console.table(XYPoints), console.table(coveringMatrix);
            /* Length of the poset line */
            let nPosetLines = UTPoset.length,
                indices = [],
                p = 0,
                index = 0;

            /* Poset Line To Poset Matrix */
            for (let i = 0; i < morder; i++) {
                POSet[i] = [];
                for (let j = 0; j < morder; j++) {
                    POSet[i][j] = (i === j || (j > i && UTPoset[nPosetLines - 1][index++] === 1)) ? 1 : 0;
                }
            }

            // console.log('The POSet was :')
            // console.table(POSet);

            /* Height of The Nodes [initial Height is 1] */
            let H = new Array(morder).fill(1);
            // let W = new Array(morder).fill(1);

            /* iterate over all columns/rows */
            for (let i = 1; i < morder; i++) {

                /* find the location of 1 which is before of diagonal 1 */
                column = POSet.map(e => e[i]);
                let hidx = column.lastIndexOf(1, i - 1);

                /* Count Height */
                if (hidx !== -1) {
                    H[i] = H[hidx] + 1;
                }

                // let widx = POSet[i].indexOf(1, i + 1);
                /* Count Width */
                // if (widx !== -1) {
                //     W[i] = W[widx] + 1;
                // }
            } // END for loop | Height of Nodes

            console.log(`H = [${H.join(', ')}]`);

            /* Height of the poset */
            Height = Math.max(...H);

            /* Sort H & swap Poset matrix as Sorted H */
            /* Create a new array of objects that include both the original values and their indices */
            const HWithIndex = H.map((value, index) => ({
                value,
                index
            }));

            // Sort the new array of objects based on the values
            HWithIndex.sort((a, b) => {
                if (a.value < b.value) {

                    /* Swap The (a.index)th row & column with (b.index) */

                    /* swap rows */
                    // console.log('Swap index a: ', a.index);
                    // console.log('with index b: ', b.index);
                    [POSet[a.index], POSet[b.index]] = [POSet[b.index], POSet[a.index]];

                    /* swap columns */
                    POSet.slice(a.index, b.index+1).forEach(row => {
                        [row[a.index], row[b.index]] = [row[b.index], row[a.index]];

                    });

                    return -1; /* swap a & b */
                } else {
                    return a.value - b.value; /* don't swap */
                }
            });

            // console.log('Now The Poset is: ')
            console.table(POSet)

            // Log the original array sorted based on the values
            H = HWithIndex.map(item => item.value);
            // console.log(`H = [${H.join(', ')}]`); // print the final values of H
            // H.sort(sortHeight);

            /* Find swapable indices  */
            for (let i = 0; i < morder - 1; i++) {
                let swapable = false;

                /* Find the indices i for which A[i][i+1] == 0 & H of (i & i+1) are same */
                if (POSet[i][i + 1] === 0 && H[i] === H[i + 1]) {
                    console.log('Swapable Row is: ', i)
                    swapable = true;
                }

                if (swapable) {
                    /* save the index which is swapable with the nPosetLines index */
                    indices[p++] = i;
                }
            } // END finding swapable indices | for Loop

            /* Find all Possible Relabel Index Group */
            const PRIndices = PRIndexGrouping(indices);
            PRIndices.shift(); /* delete first element which is [] | null */

            console.log('The indices are: ')
            console.table(PRIndices);

            // Relabel the poset for every set of indices
            PRIndices.forEach(indexSet => {
                /* Copy Poset Matrix to swapM for relabeling again */
                let swapM = JSON.parse(JSON.stringify(POSet));

                /* Relabeling Poset */
                indexSet.forEach(index => {
                    // console.log('The index is ', index)
                    /* swap rows */
                    [swapM[index], swapM[index + 1]] = [swapM[index + 1], swapM[index]];
                    // console.table(swapM[index]);
                    /* swap columns */
                    swapM.slice(index, index+2).forEach(row => {
                        [row[index], row[index + 1]] = [row[index + 1], row[index]];
                        // console.table(row[index]);
                    });
                });

                /* Poset Matrix to Poset line */
                let q = 0,
                    matrix = [];
                for (let i = 0; i < morder; i++) {
                    for (let j = i + 1; j < morder; j++) {
                        matrix[q++] = swapM[i][j];
                    }
                }

                // check the relabeled matrix already exist in UTPoset? 
                let isPoset = true,
                    newPoset = JSON.stringify(matrix); // stingify to compare the matrices 

                for (let i = 0; i < nPosetLines; i++) {
                    if (JSON.stringify(UTPoset[i]) === newPoset) {
                        isPoset = false;
                        break; // exit the for loop when the condition is met
                    }
                }

                /* Check all lower triangular entries are zero */
                for (let i = 0; i < morder - 1; i++) {
                    for (let j = 1; j > i && j < morder; j++) {
                        if (swapM[j][i] !== 0) {
                            isPoset = false;
                            console.log('NOT Poset or New Poset is:')
                            console.table(swapM)
                            console.log('How is it possible?')
                            break;
                        }
                    }

                    if (!isPoset) {
                        break;
                    }
                }

                if (isPoset) {
                    /* save the unique isomorphic posets */
                    UTPoset[nPosetLines++] = matrix;
                    console.log('New Isomorphic Poset of the above poset line: '),
                        console.table(swapM);
                }

            }); // End Relabeling -> PRIndices.forEach(indexSet
            UTPoset.sort();
            return UTPoset;
        }

        /* Delete All Elements And There Relations & Update 'XYPoints' Matrix */
        function resetCanvas() {
            // reset = true;
            document.getElementById("ShowErrors").hidden = true;
            // console.log("@@@@@@@@@@@@@@@@@\nClearing The Canvas...\n@@@@@@@@@@@@@@");
            // XYPoints = [], coveringMatrix = [];
            while (L = coveringMatrix.length) { // coveringMatrix.length dicrease to 0 as deletion
                // console.log("SEs Length in resetCanvas(): ", L);
                // console.log("Deleted Element is: ", coveringMatrix[0][0] * x, coveringMatrix[0][1] * x);

                /* All Selected Elements Sorted The Re arrange From 0 index */
                DrawPoset(coveringMatrix[0][0] * x, coveringMatrix[0][1] * x);
            }
            reDraw();
            // reset = false;
        } /* resetCanvas() END */


        /**
         * @@@@@@@@@@@@@@@@@
         *  DRAWING TOOL [By Keyboard] 
         * @@@@@@@@@@@@@@@@@@ 
         */


        /* Keyboard Features for Drawing Tool */
        function DrawingByKeyboard() {
            // console.log("Modal Showing.");
            var PreventFill = true; /* To prevent filled the element on keyboard 'Enter' in "Drawing Tool" button */
            Tab = false;
            document.getElementById("IsKeyboard").innerHTML = "Keyboard Feature is <span class='text-info'>On</span>. [Use mouse for more flexibility & guidlines]<br> <span class='text-dark'> <kbd>Enter</kbd> to select &amp; <kbd>Shift</kbd> + <kbd>Enter</kbd> to relate</span>";

            /* Keyboard Event */
            document.onkeyup = function(e) {
                /* for keyboard, e.which is deprecated */
                /* Is Shift key pressed */
                let Shift = e.shiftKey;
                // Alt = e.altKey;
                // Ctrl = e.ctrlKey;

                // console.log("key:", e.key);
                switch (e.key) {
                    case "ArrowLeft":
                        /* move selection left by 1 */
                        if (keyPosX !== 0) {
                            keyPosX--;
                        }

                        // console.log("Left Key Pressed Here. keyPosX=", keyPosX);
                        break;
                    case "ArrowUp":
                        /* move selection up by 1 */
                        if (keyPosY !== morder - 1) {
                            keyPosY++;
                        }

                        // console.log("Up Key Pressed. keyPosY=", keyPosY);
                        break;
                    case "ArrowRight":
                        /* move selection Right by 1 */
                        if (keyPosX !== morder - 1) {
                            keyPosX++;
                        }

                        // console.log("Right Key Pressed. keyPosX=", keyPosX);
                        break;
                    case "ArrowDown":
                        /* move selection Down by 1 */
                        if (keyPosY !== 0) {
                            keyPosY--;
                        }

                        // console.log("Down Key Pressed. keyPosY=", keyPosY);
                        break;
                    case "Enter":
                        /* If Shift+Enter pressed then select to relate the elements */
                        if (Shift) {
                            /* If not filled then filled first then select to relate */
                            if (!XYPoints[keyPosY][keyPosX][2]) {
                                DrawPoset(XYPoints[keyPosY][keyPosX][0] * x, XYPoints[keyPosY][keyPosX][1] * x);
                            }

                            /* Select The Current Element From Grids */
                            function Select(element) {
                                // console.log("element: ", element, " ?= ", XYPoints[keyPosY][keyPosX]);
                                return element[0] === XYPoints[keyPosY][keyPosX][0] &&
                                    element[1] === XYPoints[keyPosY][keyPosX][1]
                            }

                            if (PStart === -1 || PEnd !== -1) {
                                /* PStart was unset (For First Time) Or PEnd was Set (For Next Time, To Reset PStart) */
                                let StartTmp = coveringMatrix.findIndex(Select);
                                // console.log(StartTmp);
                                if (StartTmp !== -1) {
                                    // console.log("Start = ", StartTmp, " is set.");
                                    PStart = StartTmp;
                                }
                            } else {

                                /* PStart is set && PEnd is unset */
                                let StartTmp = coveringMatrix.findIndex(Select);
                                if (StartTmp !== -1 && StartTmp !== PStart) {
                                    // console.log("End = ", StartTmp, " is set.");
                                    PEnd = StartTmp;

                                    /* Connect The Start & End Elements */
                                    connectLine((PStart < PEnd) ? PStart : PEnd, (PStart > PEnd) ? PStart : PEnd);
                                    // console.log("The Elements ", (PStart < PEnd) ? PStart : PEnd, (PStart > PEnd) ? PStart : PEnd, " is Connected Or Dis-connected.");
                                    PStart = -1;
                                    PEnd = -1;
                                }
                            }
                        }

                        /* If 'Tab' press before 'Enter' Or modal show button is clicked by 'Enter' Or Shift key pressed with filled element then Do Nothing on 'Enter' */
                        if (PreventFill || (Tab || (Shift && XYPoints[keyPosY][keyPosX][2]))) {
                            /* Change The value After First 'Enter' Key press */
                            PreventFill = false;
                            // break;
                        } else {
                            /* Else Fill The Selection */
                            DrawPoset(XYPoints[keyPosY][keyPosX][0] * x, XYPoints[keyPosY][keyPosX][1] * x);
                            // console.log(XYPoints[keyPosY][keyPosX][0], XYPoints[keyPosY][keyPosX][1], " (XYPoints) Filled is ", XYPoints[keyPosY][keyPosX][2]);
                        }


                        break;
                    case "Tab":
                        // console.log("Tab pressed.");
                        // console.log("Tab -> True");
                        Tab = true;
                        reDraw();
                        break;
                    case "Escape":
                        Tab = false;
                        break;
                    default:
                        console.log("Please update your browser, if this site not work perfectly.")
                }

                /* Keyboard Selection Effect */
                if (!Tab) {
                    reDraw();
                    document.getElementById("IsTab").setAttribute("hidden", null);
                    poset.beginPath(),
                        poset.arc(
                            XYPoints[keyPosY][keyPosX][0] * x,
                            XYPoints[keyPosY][keyPosX][1] * x,
                            radius + 2,
                            0,
                            2 * Math.PI
                        ),
                        poset.stroke();
                } else {
                    // console.log("Tab is activated.");
                    document.getElementById("IsTab").removeAttribute("hidden");
                }
            }

            /* To reset function's variables on modal close */
            // return
        } /* DrawingByKeyboard() END */

        /* Draw Grid Circles */
        function gridsDraw() {
            for (let i = 0; i < morder; i++) {
                for (let j = 0; j < morder; j++) {
                    poset.beginPath(),
                        poset.arc(
                            XYPoints[i][j][0] * x,
                            XYPoints[i][j][1] * x,
                            radius,
                            0,
                            2 * Math.PI
                        ),
                        poset.stroke();
                }
            }
            // Initializing The Coords by small circles with radius = "$radius"
        }
        /* Initialize the canvas frame with the grids */

        // PosetInitial();
        function PosetInitial() {
            /* Canvas Border Outside the Gride system */
            poset.beginPath(); // Important

            /* Define The Canvas Area */
            poset.rect(x - 12, x - 12, width - x * 1.5, height - x * 1.5);
            poset.stroke();


            /* Side Headings & Titles */
            poset.save(); // Save all previously desinged to change design only for next poset
            (poset.textAlign = "center"),
            (poset.font = "12px 'Arial'"),
            poset.rotate((90 * Math.PI) / 180); // To rotate frame top-left (0, 0) to bottom-left (0, 0)
            (max = "Maximal Elements"), poset.fillText(max, width / 2, -3);
            /* (-ve) Height means upward */
            (max = "Minimal Elements"), poset.fillText(max, width / 2, -height + 25);
            for (let i = 0; i < morder; i++) {
                // Coordinates Labeling
                (H_no = i), poset.fillText("H:" + H_no, 17, -(x * (H_no + 1) - 6));
                (W_no = i), poset.fillText("W:" + W_no, x * (W_no + 1), -20);
            }
            poset.restore(); // restore The saved design
            // console.log("Frame ReDrawn.");
        } /* Poset Initial */

        /* ============================================================ */
        /* reDraw canvas with all drawn elements & their covers */
        /* ============================================================ */
        // console.table(coveringMatrix);
        // reDraw();

        function reDraw() {
            /* Clear The Canvas */
            poset.clearRect(0, 0, width, height);

            /* Re-Draw The Canvas Initially */
            PosetInitial();

            <?php
            if (!isset($_GET["matrix"]) || !isset($_GET["SEs"])) {
                echo "gridsDraw()";
            } else {
                echo "console.clear";
            }
            ?>
            // /* ====================================================== */
            // XYPoints = [
            //     [
            //         [
            //             1,
            //             1,
            //             false
            //         ],
            //         [
            //             1,
            //             2,
            //             true
            //         ],
            //         [
            //             1,
            //             3,
            //             false
            //         ],
            //         [
            //             1,
            //             4,
            //             false
            //         ],
            //         [
            //             1,
            //             5,
            //             false
            //         ]
            //     ],
            //     [
            //         [
            //             2,
            //             1,
            //             false
            //         ],
            //         [
            //             2,
            //             2,
            //             false
            //         ],
            //         [
            //             2,
            //             3,
            //             false
            //         ],
            //         [
            //             2,
            //             4,
            //             false
            //         ],
            //         [
            //             2,
            //             5,
            //             false
            //         ]
            //     ],
            //     [
            //         [
            //             3,
            //             1,
            //             false
            //         ],
            //         [
            //             3,
            //             2,
            //             true
            //         ],
            //         [
            //             3,
            //             3,
            //             false
            //         ],
            //         [
            //             3,
            //             4,
            //             true
            //         ],
            //         [
            //             3,
            //             5,
            //             false
            //         ]
            //     ],
            //     [
            //         [
            //             4,
            //             1,
            //             false
            //         ],
            //         [
            //             4,
            //             2,
            //             false
            //         ],
            //         [
            //             4,
            //             3,
            //             false
            //         ],
            //         [
            //             4,
            //             4,
            //             false
            //         ],
            //         [
            //             4,
            //             5,
            //             false
            //         ]
            //     ],
            //     [
            //         [
            //             5,
            //             1,
            //             false
            //         ],
            //         [
            //             5,
            //             2,
            //             true
            //         ],
            //         [
            //             5,
            //             3,
            //             false
            //         ],
            //         [
            //             5,
            //             4,
            //             true
            //         ],
            //         [
            //             5,
            //             5,
            //             false
            //         ]
            //     ]
            // ];
            // coveringMatrix = [
            //     [
            //         1,
            //         2,
            //         [
            //             1
            //         ]
            //     ],
            //     [
            //         3,
            //         2,
            //         [
            //             3,
            //             4
            //         ]
            //     ],
            //     [
            //         3,
            //         4,
            //         [
            //             3,
            //             4
            //         ]
            //     ],
            //     [
            //         5,
            //         2
            //     ],
            //     [
            //         5,
            //         4
            //     ]
            // ], SELength = coveringMatrix.length;
            // /* ====================================================== */
            if (
                coveringMatrix?.[0] != undefined
            ) {
                /* Re-Draw The Selected Elements & Relations */
                for (let i = 0; i < SELength; i++) {
                    /* Fill The Points */
                    poset.beginPath(),
                        poset.arc(
                            coveringMatrix[i][0] * x,
                            coveringMatrix[i][1] * x,
                            radius,
                            0,
                            2 * Math.PI
                        ),
                        poset.fill();

                    /* Re-Draw The Covers of i if Exist */
                    if (
                        coveringMatrix[i][2]?.[0] != undefined
                    ) {
                        /* If Any Relation Found To 'i' get Length of the Relation List */
                        var jL = coveringMatrix[i][2].length;

                        for (let j = 0; j < jL; j++) {
                            poset.beginPath(),
                                poset.moveTo(
                                    coveringMatrix[i][0] * x,
                                    coveringMatrix[i][1] * x
                                );
                            poset.lineTo(
                                coveringMatrix[coveringMatrix[i][2][j]][0] * x,
                                coveringMatrix[coveringMatrix[i][2][j]][1] * x
                            );

                            poset.stroke();
                            // console.log("The Relation is restored.");
                        } /* Covers of i Re-Drawn */
                    } /*  No Cover is Found For i */
                } /* Re-stored All */
            }
        } // reDraw()

        /* ============================================================ */
        /* Find Indices of Least Distanced (< radius) of two Nearest (minDistance) Points from "coveringMatrix" matrix */
        /* ============================================================ */
        function LeastDistance(xCurrent, yCurrent) {
            let Start = -1,
                End = -1;
            let X1, Y1, X2, Y2;
            let minDistance = Number.MAX_SAFE_INTEGER,
                DistanceFromPointToLine = Number.MAX_SAFE_INTEGER,
                DistanceFromXY1ToXY2 = Number.MAX_SAFE_INTEGER;
            /* X Coordinate of Clicked Position */
            let xCCo = Math.floor(xCurrent / 50);
            let count = 0; // selected Elements below xCurrent
            coveringMatrix.forEach((element) => {
                if (element[0] <= xCCo) {
                    count++;
                }
            });
            /* Get Two Points To Relate */
            for (let i = count - 1; i >= 0; i--) {
                /* Set X1, Y1 as 1st point down xCurrent */
                X1 = coveringMatrix[i][0] * x;
                Y1 = coveringMatrix[i][1] * x;

                /* Find Suitable X2, Y2 for 2nd point in up xCurrent */
                for (let j = count; j < SELength; j++) {
                    if (coveringMatrix[j][0] * x > X1) {

                        /* take End point */
                        X2 = coveringMatrix[j][0] * x;
                        Y2 = coveringMatrix[j][1] * x;

                        /* Disntance From Start to End */
                        DistanceFromXY1ToXY2 = Math.sqrt(
                            Math.pow(X1 - X2, 2) + Math.pow(Y1 - Y2, 2)
                        );

                        /* Distance From Hovered Point To Start-End Line */
                        DistanceFromPointToLine =
                            Math.abs(
                                (Y2 - Y1) * xCurrent -
                                (X2 - X1) * yCurrent +
                                (Y1 * (X2 - X1) - X1 * (Y2 - Y1))
                            ) / DistanceFromXY1ToXY2;

                        /* connect points with min distanced & must be less than "radius" distance */
                        if (
                            DistanceFromXY1ToXY2 < minDistance &&
                            DistanceFromPointToLine <= radius
                        ) {
                            /* =========================================== */
                            /* Check! is Trivial Case [Cover of Sub-cover is also a cover] */
                            /* =========================================== */

                            /* var isCovered = false => i & j is connectable */
                            var isCovered = (IsConnectable(i, j) === true) ? true : false;
                            // console.log("Check! Can We Relate?", i, j, !isCovered); // If Covered We can't Relate.

                            /* @@@@@@@@@@@@@@@@@@@@@@@ */
                            /* Making Relation */
                            if (isCovered) {
                                document.getElementById("ShowErrors").innerHTML = "Can't Relate Them.";
                                // document.getElementById("ShowErrors").innerHTML = "Transitivity is trivial. Trivial cases are ignored for drawing simplicity.";
                                // document.getElementById("ShowErrors").hidden = false;
                            } else {
                                /* Save Smallest Path & Update In Every Possible Path */
                                // console.log("Start & End Updated. Since (minDistance =)", minDistance, ">", DistanceFromXY1ToXY2, "(DistanceFromXY1ToXY2)");
                                minDistance = DistanceFromXY1ToXY2;

                                /* Update the points To Draw */
                                poset.beginPath(); /* Use beginPath() to prevent connection from previous path */
                                poset.moveTo(X1, Y1);
                                poset.lineTo(X2, Y2);

                                /* Save The Indices of X1, Y1 and X2, Y2 to return to finally drawn */
                                (Start = i), (End = j);
                                // console.log("Start =", Start, " End = ", End);
                                // console.table(coveringMatrix);
                                // console.table(coveringMatrix[0][2]);
                            } // The Relation is Trivial
                            /* Making Relation END */
                            /* @@@@@@@@@@@@@@@@@@@@@@@ */

                        } // Click near the connection line or on nearest points // check is minDistanced
                    } // Relate only in upword direction
                } // For loop of j -> SELength Closed
            } // For loop of i -> 0 Closed
            return [Start, End];
        } // LeastDistance()

        /* Hover Efect on canvas */
        function ShowConnection(xCurrent, yCurrent) {
            /* Is Connectable In Hover */
            let [Start, End] = LeastDistanceShow(xCurrent, yCurrent);
            /* If Possible Elements Found To Relate */
            if (Start > -1 && End > -1) {
                // document.getElementById("Coords").innerHTML =
                //     "The Nearest Points are: [" + Start + " ," + End + "]";
                // console.log("The Nearest Points are: [", Start, " ,", End, "]");

                /* If New Pair of Elements Found */
                if (PStart != Start || PEnd != End) {
                    // console.log(PStart, "!=", Start, "&", PEnd, "!=", End);
                    Draw = 1;
                    /* Remove previous Dashed Lines */
                    reDraw();
                    /* Draw The New Dashed Line */
                    poset.save();
                    poset.beginPath(); /* Use beginPath() to prevent connection from previous path */
                    poset.moveTo(
                        coveringMatrix[Start][0] * x,
                        coveringMatrix[Start][1] * x
                    );
                    poset.lineTo(coveringMatrix[End][0] * x, coveringMatrix[End][1] * x);
                    poset.setLineDash([3, 3]);
                    poset.stroke();
                    poset.restore();
                } else {
                    /* If Same Points Found Do Nothing i.e no draw or no reDraw() */
                }
            } else if (Draw == 1) {
                /* If Dashed line Already Drawn & Next Mouse move Detect Nothing i.e. Start & End = -1 */
                Draw = 0;
                reDraw();
            } // if (Start != -1 && End != -1) END
            /* Save The Previous Mouse Position */
            // console.log("Previous Mouse Position is Saved in ", Start, End);
            (PStart = Start), (PEnd = End);
        } // ShowConnection(xCurrent, yCurrent) END

        /* 1D or 2D array Sorter */
        function Sorting2DArray(a, b) {

            // 2D array Sorter in 3D array
            if (a?.[0] != undefined && b?.[0] != undefined) {

                // 2D array Sorter [a, b are two 1D array]
                if (a[0] === b[0]) {

                    /* If in same Height sort ascending by Width */
                    // console.log("Sorting");
                    return a[1] - b[1];

                } else {

                    return a[0] - b[0];

                }

            } else {
                // 1D array Sorter [a, b are two elements]
                return a - b;
            }
        }

        // function Sorting2DArray(a, b) {
        //     if (a[2]?.[0] != undefined && b[2]?.[0] != undefined) {
        //         // 1D array in 3D array 
        //         a[2].sort(Sorting2DArray);
        //         b[2].sort(Sorting2DArray);
        //     }

        //     if (a?.[0] != undefined && b?.[0] != undefined) {
        //         // 2D array Sorter in 3D array
        //         if (a[0] === b[0]) {
        //             /* If in same Height sort accending by Width */
        //             console.log("Sorting");
        //             return a[1] - b[1];
        //         } else {
        //             return a[0] - b[0];
        //         }
        //     } else {
        //         // 1D array Sorter in 2D array
        //         return a - b;
        //     }
        // }

        /* ============================================================  */
        /* Fill The Clicked Unfilled Gride Point Or Unfill Clicked Filled Gride Point  */
        /* ============================================================  */
        function fillOrUnFill(xCoord, yCoord) {
            // console.log("In fillOrUnFill(): ", xCoord, yCoord);

            /* Find the click Point's Position in Selected Elements */
            function FindI_SE(SEs) {
                return (
                    SEs[0] == XYPoints[xCoord][yCoord][0] &&
                    SEs[1] == XYPoints[xCoord][yCoord][1]
                );
            }

            /* Fill if not filled Maximum upto MOrder nodes/Elements */
            if (XYPoints[xCoord][yCoord][2] === false && SELength !== morder) {
                // console.log(SELength);
                let X = XYPoints[xCoord][yCoord][0] * x,
                    Y = XYPoints[xCoord][yCoord][1] * x;

                /* Now Draw The Node as Filled */
                poset.beginPath(), poset.arc(X, Y, radius, 0, 2 * Math.PI), poset.fill();

                /* Selected Nodes Save to 'coveringMatrix[]' */
                coveringMatrix[SELength++] = [
                    XYPoints[xCoord][yCoord][0],
                    XYPoints[xCoord][yCoord][1],
                ];
                // console.log("The filled point is ");
                // console.table(coveringMatrix[SELength - 1]);
                XYPoints[xCoord][yCoord][2] = true; /* Fill Successfull */
                var U = 1; /* 1 element will be added in p Position */
                // Total Number of Filled Nodes
                /* Ufill/Remove The node From coveringMatrix array if already filled */
            } else if (XYPoints[xCoord][yCoord][2] == true) {
                /* Find The Index Of The Point on Selectedelements */
                var p = coveringMatrix.findIndex(FindI_SE);
                /* Remove All Relations From or To This Point */
                U = -1; // Delete 1 Element
                /* Update The Covers According To p & U if there exist any relation to update */
                if (SELength > 1) {
                    /* Update The Relations in selected Elements According To (p, U) */
                    UpdateRelations(p, U);
                    // console.log("All Relations are Updated. Now Remove", p, "From:"),
                    //   console.table(coveringMatrix);
                }
                /* Now Remove P Element & All Relations From it */
                // console.table(coveringMatrix[p]);
                // if (
                //   coveringMatrix[p][2] != undefined &&
                //   coveringMatrix[p][2] != null &&
                //   coveringMatrix[p][2].length
                // ) {
                //   console.log("with the relations "), console.table(coveringMatrix[p][2]);
                // }

                coveringMatrix.splice(p--, 1);
                SELength--;
                XYPoints[xCoord][yCoord][2] = false; /* Unfill Successfull */
                // console.log("Now Redrawn Poset is:"), console.table(coveringMatrix);
                reDraw();
            } else {
                /* If More than morder is selected */
                document.getElementById("ShowErrors").hidden = false;
                document.getElementById("ShowErrors").innerHTML = morder + " Elements are already selected.";
                return
            }

            /* Sort The Filled Nodes Array */
            coveringMatrix.sort(Sorting2DArray);
            // console.table(coveringMatrix);

            let P = coveringMatrix.findIndex(FindI_SE);
            if (SELength > 2 && P > -1) {
                /* Update The Relations in selected Elements According To (p, U) */
                // console.log("Updating Relations For", P);
                UpdateRelations(P, U);
            }

            /* Copy Nodes To a new array for showConnection() to get hover effect */
            // let Copy_nodes_i = 0;
            // coveringMatrix.forEach((element) => {
            //     // get only filled elements without connections
            //     HEs[Copy_nodes_i] = [];
            //     HEs[Copy_nodes_i][0] = element[0];
            //     HEs[Copy_nodes_i][1] = element[1];
            //     Copy_nodes_i++;
            // });
        } // fillOrUnFill(xCoord, yCoord)

        /** 
         * ===========================================================
         * Connect Two Selected Points (Start (<) End, are coveringMatrix's index)
         * ===========================================================
         */
        function connectLine(Start, End) {
            if (
                coveringMatrix[Start][2]?.[0] != undefined
            ) {
                /* Check They are already connected (return Index) or Not (return -1). */
                /* Find The Index Of the element to remove */
                let k = coveringMatrix[Start][2].findIndex(function(H) {
                    return H == End;
                });
                if (k != -1) {
                    /* If They already Connected Then Remove The Connection. */
                    coveringMatrix[Start][2].splice(k--, 1);
                } else {
                    /* If New Connection (k = -1) Then Push To The Array Start.e. connect them. */
                    coveringMatrix[Start][2].push(End);
                    connected = 1;
                }
            } else {
                coveringMatrix[Start][2] = [End];
                connected = 1; /* Start & End Already Connected */
            }
            /* Sort The Covering Elements for convenience searching on letter functions */
            if (!reset) {
                coveringMatrix[Start][2].sort(Sorting2DArray);
            }
            // console.table(coveringMatrix[Start][2]);
            /* A New Connection Created (1) or A Connection Removed (-1) */
            if (connected == 1) {
                poset.stroke();
            } else {
                reDraw();
            }
        } /* ConnectLine(Start, End) END */

    } // END if (typeof(poset) !== undefined)

    /* ============================================================ */
    /* Is Start & End connectable? return true or false */
    /* ============================================================ */
    function IsConnectable(Start, End) {
        // console.log("Here Start =", Start, " & End =", End);
        /* is Start Cover By Any Elements? */
        if (
            coveringMatrix[Start][2]?.[0] != undefined
        ) {
            // console.log(Start, "is covered by: ");
            // console.table(coveringMatrix[Start][2]);
            let StartL = coveringMatrix[Start][2].length;
            for (let ii = 0; ii < StartL; ii++) {
                /* ii'th Element is cover of "Start" */
                let cover = coveringMatrix[Start][2][ii];
                // console.log("Searching ",cover);

                /* is "cover" also cover of "End" => made a triangle as 'CUP' shape */
                if (
                    coveringMatrix[End][2]?.[0] != undefined
                ) {
                    // console.log(End, "is covered by: "),
                    // console.table(coveringMatrix[End][2]);

                    /* if "cover" is also cover of End */
                    let foundI = coveringMatrix[End][2].findIndex(function(element) {
                        return element === cover;
                    });
                    if (foundI != -1) {
                        // console.log("Can't Relate.");
                        return true;
                    }
                }

                /**
                 * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
                 * May Possible To Simplify From Here [Do the same in the function?]
                 * @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
                 */

                /* Is End Cover "Start's Cover" or any of it'a cover. => made a 'initial-moon' shape*/
                // console.table(coveringMatrix);
                // console.table(coveringMatrix[ii]);
                if (
                    coveringMatrix[cover][2]?.[0] != undefined
                ) {
                    // console.log(cover, "is covered by: "),
                    //   console.table(coveringMatrix[cover][2]);

                    /* is 2nd Element (Start's Cover) is covered by End */
                    let foundI = coveringMatrix[cover][2].findIndex(function(element) {
                        // console.log(element, " ?= ", End);
                        return element === End;
                    });
                    // console.log(End, "is 2nd gen? if yes then with?: ");
                    // console.log(foundI);

                    if (foundI !== -1) {
                        // console.log(End, "cover", cover, "\nSo Can't Relate.");
                        return true; // Not Covered
                    } else {
                        // console.log(
                        //   "At least One Element Found Which Cover the cover of Start & Less Than End."
                        // );
                        /* If All is > End Then Start & End Can be connected */
                        /* cover is not directly covered by End */
                        let relL = coveringMatrix[cover][2].length;
                        for (let iii = 0; iii < relL; iii++) {
                            let rel1 = coveringMatrix[cover][2][iii];
                            /* Is rel1 Cover End then must be End is not cover rel1 => End is not cover "cover" (Cover of Start) => End is not cover Start */
                            // return (rel1 < End && relations(rel1))? true : false;
                            if (rel1 < End && relations(rel1)) return true;

                            /* ==================================================== */
                            /* Find The Relations by Recurcive Sub-Function */
                            /* ==================================================== */
                            function relations(rel1) {
                                if (
                                    coveringMatrix[rel1][2]?.[0] != undefined
                                ) {
                                    // console.log(rel1, "is covered by"),
                                    //     console.table(coveringMatrix[rel1][2]);
                                    let rel1L = coveringMatrix[rel1][2].length;

                                    /* Check if rel1 in covered by END */
                                    let foundII = coveringMatrix[rel1][2].findIndex(function(
                                        element
                                    ) {
                                        return element == End;
                                    });
                                    if (foundII !== -1) {
                                        // console.log(End, "is cover", rel1);
                                        return true;
                                    } else {
                                        // console.log("Is", rel1, " cover / covered by any element?");
                                        for (let iii = 0; iii < rel1L; iii++) {
                                            let rel2 = coveringMatrix[rel1][2][iii];
                                            if (rel2 < End) {
                                                /* If rel2 not cover End Or Equal End Then Search Again */
                                                return relations(rel2);
                                            } else if (rel2 == End) {
                                                /* If rel2 == End */
                                                return true;
                                            }
                                        }
                                    }
                                }
                            } // relations(cover) End
                        }
                    }
                }
            } // For loop (ii->StartL) End
        }

        /* is Any Elements Cover By Start & End ? made a triangle as 'U' shape */
        for (let i = 0; i < Start; i++) {
            if (
                coveringMatrix[i][2]?.[0] != undefined
            ) {
                // console.log(i, "is covered by: "),
                // console.table(coveringMatrix[i][2]);
                /* Is 'Start' covers i'th element */
                let foundS = coveringMatrix[i][2].findIndex(
                    (element) => element == Start
                );
                /* Is 'End' covers i'th element */
                let foundE = coveringMatrix[i][2].findIndex(
                    (element) => element == End
                );

                /* Is Start & End Cover Same Element i */
                if (foundS > -1 && foundE > -1) {
                    // console.log("Can't Relate.");
                    return true;
                }
                // else {
                //   console.log("No Element is Found Which is covered by ", Start, End);
                // }
            } // if(coveringMatrix[i][2] != undefined|null|0)
        }
        // return isCovered;
    } // IsConnectable() END

    /* ============================================================  */
    /* Find Distance From Clicked Point To Others Gride Points */
    /* ============================================================  */
    function findDistFromGrids(xCurrent, yCurrent) {
        // console.log("In findDistFromGrids() ", xCurrent, yCurrent);
        // Current Point (x, y)
        for (let k = 0, i = 0; i < morder; i++) {
            for (let j = 0; j < morder; j++, k++) {
                (X = XYPoints[i][j][0] * x), (Y = XYPoints[i][j][1] * x);
                DistanceFromGrids[k] = Math.sqrt(
                    Math.pow(X - xCurrent, 2) + Math.pow(Y - yCurrent, 2)
                );
            }
        }
        d = Math.min(...DistanceFromGrids);
        K = DistanceFromGrids.indexOf(d);
        (xCoord = Math.floor(K / morder)), (yCoord = K % morder); // Calculate the XYPoints index of the value from the DistanceFromGrids index
        return [xCoord, yCoord, K];
    } // findDistFromGrids(xCurrent, yCurrent) END

    /* ============================================================  */
    /* Update The Covers Of the Elements According To The Value Of (p, U)  */
    /* ============================================================  */
    function UpdateRelations(p, U) {
        /* If Removed Selected Elements */
        /* Will be deleted from 'p' Position */
        if (U < 0) {
            // console.log(
            //   "#########################\nTo be Updated For Deletion from",
            //   p,
            //   "\n########################"
            // ),
            // console.table(coveringMatrix);
            for (let j = 0; j < SELength - 1; j++) {
                /* Heightest element has no cover so SELength-1 */
                /*
                      => If exist any relation with p find that.
                      => remove the relations
                      => decreas the relational value (>p) by 1
                      => remove p'th element
                      */
                if (
                    coveringMatrix[j][2]?.[0] != undefined && j != p
                ) {
                    // console.log("Cover of ", j, "============================\n");
                    /* j'th element has relation to p */
                    /* Search Is j'th element Covered by (<p & j->p) 'p' */
                    let m = coveringMatrix[j][2].findIndex((element) => element == p);
                    if (m > -1 && j < p) {
                        /* If 'p' is cover j */
                        // console.log("Remove ", p, "-cover of", j);
                        /* Delete the p-cover */
                        coveringMatrix[j][2].splice(m--, 1);
                    } // m > -1 && j < p

                    // Here p!=j by default in 389 condition
                    // console.log(
                    //   "The Covers of ",
                    //   j,
                    //   "are: \n[Need To be Dicreases by 1 Which are >",
                    //   p,
                    //   "]"
                    // ),
                    //   console.table(coveringMatrix[j][2]);
                    /* 
                    Search: 
                    => Is j'th element Covered by SEs whose are >p. (Since All SEs > p will reduce by 1). 
                    => If found reduce Relational values by 1 
                    */
                    let RL = coveringMatrix[j][2].length;
                    m = coveringMatrix[j][2].findIndex((element) => element > p);
                    if (m > -1) {
                        /* j'th (>p) element's relational values will be reduced by 1 */
                        for (let q = m; q < RL; q++) {
                            // console.log(coveringMatrix[j][2][q], "is Decreased to");
                            coveringMatrix[j][2][q]--;
                            // console.log(coveringMatrix[j][2][q], "in", j);
                        }
                        //   console.table(coveringMatrix[j][2]);
                    } // q->RL
                } // if(coveringMatrix[j][2] != undefined)
            } // j -> SELength-1
        } else if (U > 0) {
            /* If a new element inserted in 'p' Position */
            // console.log(
            //   "#######################\nTo be Updated For Insertion in",
            //   p,
            //   "\n########################"
            // ),
            // console.table(coveringMatrix);
            /* Update for every Elements */
            for (let j = 0; j < SELength - 1; j++) {
                /* Heightest element has no cover so SELength-1 */
                /*
                      => If exist any relation to >= p find that.
                      => increase the relational value by 1
                      */
                if (
                    coveringMatrix[j][2]?.[0] != undefined &&
                    j != p
                ) {
                    let pL = coveringMatrix[j][2].length;
                    /* Increase Relational Values by 1, Since Inserted in p */
                    let m = coveringMatrix[j][2].findIndex((element) => element >= p);
                    // If covers not sorted then use filter
                    /* Since All Covers are sorted in accending order */
                    if (m > -1) {
                        // console.log("Increase Them by 1 whose are >=", p),
                        // console.table(coveringMatrix[j]);
                        for (let ji = m; ji < pL; ji++) {
                            // console.log(coveringMatrix[j][2][ji], "is increased to");
                            coveringMatrix[j][2][ji]++;
                            // console.log(coveringMatrix[j][2][ji]);
                        }
                        // console.log("Updated Now"),
                        // console.table(coveringMatrix[j]);
                    } //(m > -1)
                } // if(coveringMatrix[j][2] != undefined)
            } // j-> SELength-1
        } // U>0 nothing do for U == 0
    } /* UpdateRelations(p) END */

    /* ============================================================  */
    /* ############################# To Draw Line Between Possible Related Points on Click ############################# */
    /* ============================================================  */
    // if (typeof(poset) !== undefined) {

    const Canvas = document.querySelector("#poset");
    // console.log(Canvas);
    if (Canvas !== null) {
        Canvas.addEventListener("mouseup", function(e) {
            // console.log("Mouse Up Event.");
            let [xCurrent, yCurrent] = getMousePosition(Canvas, e);
            /* "event.which == 1" Only left mouse click is enabled on inner small square area*/

            if (
                xCurrent >= x - 5 &&
                yCurrent > x - 5 &&
                xCurrent <= width - x + 5 &&
                yCurrent <= height - x + 5 &&
                (e.which === 1 || e.button === 0)
            ) {
                // document.getElementById("Coords").innerHTML =
                //     "The Selected Point is: [" +
                //     Math.round(xCurrent / x) +
                //     " ," +
                //     Math.round(yCurrent / x) +
                //     "]";
                // console.log(xCurrent, yCurrent);
                DrawPoset(xCurrent, yCurrent);
            }
            // else {
            // console.log("Do Nothing In Mouse Up Event.");
            // }
        });

        /* ============================================================  */
        /* To Draw Dotted Line Between Possible Related Points on Hover */
        /* ============================================================  */
        const canvasHover = Canvas;
        canvasHover.addEventListener("mousemove", function(e) {
            // console.log("Mouse Move Event.");
            let [xCurrent, yCurrent] = getMousePosition(canvasHover, e);
            /* Active the function on small square after minimum 2 element selection. */
            if (
                xCurrent >= x - 5 &&
                yCurrent >= x - 5 &&
                xCurrent <= width - x + 5 &&
                yCurrent <= height - x + 5 && coveringMatrix.length > 1
            ) {
                ShowConnection(xCurrent, yCurrent);
            }
            // else {
            // console.log("Do Nothing in Mouse Move.");
            // }
        });
    }
    <?php
    // exit(); 
    ?>
</script>