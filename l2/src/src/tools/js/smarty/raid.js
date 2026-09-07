function raidcalc(amountOfDisks, singleDiskSpace, raidLevel, targetElementId) {
    amountOfDisks = parseInt(amountOfDisks);
    singleDiskSpace = parseFloat(singleDiskSpace);

    let capacity = 0;
    let factor = 1;
    let unit = ' B';

    if (amountOfDisks < 2 && (raidLevel == '0' || raidLevel == '1')) {
        $(targetElementId).update('Disk amount must be at least 2.');
        return;
    }

    if (amountOfDisks < 3 && (raidLevel == '2' || raidLevel == '5')) {
        $(targetElementId).update('Disk amount must be at least 3.');
        return;
    }

    if (amountOfDisks < 4 && (raidLevel == '6' || raidLevel == '10')) {
        $(targetElementId).update('Disk amount must be at least 4.');
        return;
    }

    if (singleDiskSpace >= 1024) {
        factor = 1024;
        unit = ' KB';
    }

    if (singleDiskSpace >= Math.pow(1024, 2)) {
        factor = Math.pow(1024, 2);
        unit = ' MB';
    }

    if (singleDiskSpace >= Math.pow(1024, 3)) {
        factor = Math.pow(1024, 3);
        unit = ' GB';
    }

    if (singleDiskSpace >= Math.pow(1024, 4)) {
        factor = Math.pow(1024, 4);
        unit = ' TB';
    }

    const formatCapacity = (value) => (value / factor).toFixed(2) + unit;

    const makeEven = (number) => (number % 2 !== 0) ? number - 1 : number;

    // @see ID-11228 Calculcation was taken from https://de.wikipedia.org/wiki/RAID#Zusammenfassung

    switch (raidLevel) {
        case '0':
            capacity = (amountOfDisks * singleDiskSpace);

            $(targetElementId).update(formatCapacity(capacity));
            return;

        case '1':
            capacity = (singleDiskSpace);

            $(targetElementId).update(formatCapacity(capacity));
            return;

        case '2':
            capacity = ((amountOfDisks - Math.log2(amountOfDisks)) * singleDiskSpace);

            $(targetElementId).update(formatCapacity(capacity));
            return;

        case '5':
            capacity = ((amountOfDisks - 1) * singleDiskSpace);

            $(targetElementId).update(formatCapacity(capacity));
            return;

        case '6':
            capacity = ((amountOfDisks - 2) * singleDiskSpace);

            $(targetElementId).update(formatCapacity(capacity));
            return;

        case '10':
            capacity = ((makeEven(amountOfDisks) / 2) * singleDiskSpace);

            $(targetElementId).update(formatCapacity(capacity));
            return;
    }

    $(targetElementId).update('0.00');
}
