<?php

/**
 * Basic participants structure
 */
return [
    [
        /**
         * @rewards BASIC, CUSTOM
         */
        'id' => 1,
        'lft' => 1,
        'rgt' => 45,
        'depth' => 0,
    ],
        // --- 1 ---
        [
            /**
             * @branch 1
             * @rewards BASIC
             */
            'id' => 11,
            'lft' => 2,
            'rgt' => 3,
            'depth' => 1,
        ],
        [
            /**
             * @branch 1
             * @rewards BASIC, CUSTOM
             */
            'id' => 12,
            'lft' => 4,
            'rgt' => 9,
            'depth' => 1,
        ],
            // --- 2 ---
            [
                /**
                 * @branch 12
                 * @rewards BASIC, CUSTOM
                 */
                'id' => 121,
                'lft' => 5,
                'rgt' => 8,
                'depth' => 2,
            ],
                // --- 3 ---
                [
                    /**
                     * @branch 121
                     * @rewards ALL
                     * @percentile 0.1
                     */
                    'id' => 1211,
                    'lft' => 6,
                    'rgt' => 7,
                    'depth' => 3,
                ],
        // --- 1 ---
        [
            /**
             * @branch 1
             * @rewards BASIC
             */
            'id' => 13,
            'lft' => 10,
            'rgt' => 24,
            'depth' => 1,
        ],
            // branch 13
            [
                /**
                 * @branch 13
                 * @rewards NONE
                 */
                'id' => 131,
                'lft' => 11,
                'rgt' => 23,
                'depth' => 2,
            ],
                // --- 3 ---
                [
                    /**
                     * @branch 131
                     * @rewards NONE
                     */
                    'id' => 1311,
                    'lft' => 12,
                    'rgt' => 13,
                    'depth' => 3,
                ],
                [
                    /**
                     * @branch 131
                     * @rewards NONE
                     */
                    'id' => 1312,
                    'lft' => 14,
                    'rgt' => 15,
                    'depth' => 3,
                ],
                [
                    /**
                     * @branch 1313
                     * @rewards BASIC, CUSTOM
                     */
                    'id' => 1313,
                    'lft' => 16,
                    'rgt' => 21,
                    'depth' => 3,
                ],
                    // --- 4 ---
                    [
                        /**
                         * @branch 1313
                         * @rewards BASIC, CUSTOM
                         */
                        'id' => 13131,
                        'lft' => 17,
                        'rgt' => 20,
                        'depth' => 4,
                    ],
                        // --- 5 ---
                        [
                            /**
                             * @branch 13131
                             * @rewards ALL
                             * @percentile 0.2
                             */
                            'id' => 131311,
                            'lft' => 18,
                            'rgt' => 19,
                            'depth' => 5,
                        ],
        // --- 1 ---
        [
            /**
             * @branch 1
             * @rewards BASIC
             */
            'id' => 14,
            'lft' => 25,
            'rgt' => 44,
            'depth' => 1,
        ],
            // --- 2 ---
            [
                /**
                 * @branch 14
                 * @rewards CUSTOM
                 */
                'id' => 141,
                'lft' => 26,
                'rgt' => 43,
                'depth' => 2,
            ],
                // --- 3 ---
                [
                    /**
                     * @branch 141
                     * @rewards BASIC, CUSTOM
                     */
                    'id' => 1411,
                    'lft' => 27,
                    'rgt' => 28,
                    'depth' => 3,
                ],
                [
                    /**
                     * @branch 141
                     * @rewards BASIC, CUSTOM
                     */
                    'id' => 1412,
                    'lft' => 29,
                    'rgt' => 30,
                    'depth' => 3,
                ],
                [
                    /**
                     * @branch 141
                     * @rewards BASIC, CUSTOM
                     */
                    'id' => 1413,
                    'lft' => 31,
                    'rgt' => 42,
                    'depth' => 3,
                ],
                    // --- 4 ---
                    [
                        /**
                         * @branch 1413
                         * @rewards CUSTOM
                         */
                        'id' => 14131,
                        'lft' => 32,
                        'rgt' => 41,
                        'depth' => 4,
                    ],
                        // --- 5 ---
                        [
                            /**
                             * @branch 14131
                             * @rewards BASIC, CUSTOM
                             */
                            'id' => 141311,
                            'lft' => 33,
                            'rgt' => 36,
                            'depth' => 5,
                        ],
                            // --- 6 ---
                            [
                                /**
                                 * @branch 141311
                                 * @rewards BASIC, CUSTOM
                                 */
                                'id' => 1413111,
                                'lft' => 34,
                                'rgt' => 35,
                                'depth' => 6,
                            ],
                        // --- 5 ---
                        [
                            /**
                             * @branch 14131
                             * @rewards BASIC, CUSTOM
                             */
                            'id' => 141312,
                            'lft' => 37,
                            'rgt' => 40,
                            'depth' => 5,
                        ],
                            // --- 6 ---
                            [
                                /**
                                 * @branch 141312
                                 * @rewards BASIC, CUSTOM
                                 */
                                'id' => 1413121,
                                'lft' => 38,
                                'rgt' => 39,
                                'depth' => 6,
                            ],
];
